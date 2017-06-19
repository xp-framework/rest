<?php namespace webservices\rest\unittest;

use webservices\rest\TypeMarshaller;
use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\MapType;
use lang\XPClass;
use lang\ClassLoader;
use util\Date;
use util\TimeZone;
use util\Money;
use util\Currency;
use webservices\rest\RestMarshalling;
use webservices\rest\unittest\srv\fixture\Wallet;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestMarshalling
 */
class RestMarshallingTest extends \unittest\TestCase {
  private static $enumClass;
  private static $walletClass;
  private static $moneyMarshaller;
  private static $walletMarshaller;
  private $fixture;

  /**
   * Sets up test case
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= new RestMarshalling();
  }

  #[@beforeClass]
  public static function defineEnumClass() {
    self::$enumClass= ClassLoader::defineClass('ExampleEnum', 'lang.Enum', [], '{
      public static $ELEMENT1;
      public static $ELEMENT2;
      
      static function __static() {
        static::$ELEMENT1= new static(0, "ELEMENT1");
        static::$ELEMENT2= new static(1, "ELEMENT2");
      }
    }');
  }

  #[@beforeClass]
  public static function defineWalletClassMarshaller() {
    self::$walletClass= new XPClass(Wallet::class);
    self::$walletMarshaller= newinstance(TypeMarshaller::class, [], [
      'marshal' => function($wallet, $marshalling= null) {
        return $marshalling->marshal($wallet->values);
      },
      'unmarshal' => function(\lang\Type $t, $input, $marshalling= null) {
        return $t->newInstance($marshalling->unmarshal(new \lang\ArrayType('util.Money'), $input));
      }
    ]);
  }

  #[@beforeClass]
  public static function defineMoneyMarshaller() {
    self::$moneyMarshaller= newinstance(TypeMarshaller::class, [], [
      'marshal' => function($money, $marshalling= null) {
        $amount= $money->amount();
        return sprintf('%.2f %s', is_object($amount) ? $amount->doubleValue() : $amount, $money->currency()->name());
      },
      'unmarshal' => function(\lang\Type $t, $input, $marshalling= null) {
        sscanf($input, '%f %s', $amount, $currency);
        return $t->newInstance($amount, \util\Currency::getInstance($currency));
      }
    ]);
  }

  #[@test]
  public function marshal_null() {
    $this->assertEquals(null, $this->fixture->marshal(null));
  }

  #[@test]
  public function marshal_string() {
    $this->assertEquals('Hello', $this->fixture->marshal('Hello'));
  }

  #[@test]
  public function marshal_int() {
    $this->assertEquals(6100, $this->fixture->marshal(6100));
  }

  #[@test]
  public function marshal_double() {
    $this->assertEquals(1.5, $this->fixture->marshal(1.5));
  }

  #[@test]
  public function marshal_bool() {
    $this->assertEquals(true, $this->fixture->marshal(true));
  }

  #[@test]
  public function marshal_string_array() {
    $this->assertEquals(['Hello', 'World'], $this->fixture->marshal(['Hello', 'World']));
  }

  #[@test, @values([
  #  [new \ArrayIterator(['Hello', 'World'])]
  #])]
  public function marshal_traversable_array($in) {
    $this->assertEquals(['Hello', 'World'], iterator_to_array($this->fixture->marshal($in)));
  }

  #[@test, @values([
  #  [new \ArrayIterator(['Hello' => 'World'])]
  #])]
  public function marshal_traversable_map($in) {
    $this->assertEquals(['Hello' => 'World'], iterator_to_array($this->fixture->marshal($in)));
  }

  #[@test]
  public function marshal_string_map() {
    $this->assertEquals(
      ['greeting' => 'Hello', 'name' => 'World'],
      $this->fixture->marshal(['greeting' => 'Hello', 'name' => 'World'])
    );
  }

  #[@test]
  public function marshal_date_instance() {
    $this->assertEquals(
      '2012-12-31T18:00:00+01:00',
      $this->fixture->marshal(new Date('2012-12-31 18:00:00', new TimeZone('Europe/Berlin')))
    );
  }

  #[@test]
  public function marshal_date_array() {
    $this->assertEquals(
      ['2012-12-31T18:00:00+01:00'],
      $this->fixture->marshal([new Date('2012-12-31 18:00:00', new TimeZone('Europe/Berlin'))])
    );
  }

  #[@test]
  public function marshal_issue_with_field() {
    $issue= new IssueWithField(1, 'test');
    $this->assertEquals(
      ['issueId' => 1, 'title' => 'test'], 
      $this->fixture->marshal($issue)
    );
  }

  #[@test]
  public function marshal_issue_with_getter() {
    $issue= new IssueWithGetter(1, 'test');
    $this->assertEquals(
      ['issueId' => 1, 'title' => 'test', 'createdAt' => null], 
      $this->fixture->marshal($issue)
    );
  }

  #[@test]
  public function marshal_array_of_issues() {
    $issues= [
      new IssueWithField(1, 'test1'),
      new IssueWithField(2, 'test2')
    ];
    $this->assertEquals(
      [['issueId' => 1, 'title' => 'test1'], ['issueId' => 2, 'title' => 'test2']],
      $this->fixture->marshal($issues)
    );
  }

  #[@test]
  public function marshal_map_of_issues() {
    $issues= [
      'one' => new IssueWithField(1, 'test1'),
      'two' => new IssueWithField(2, 'test2')
    ];
    $this->assertEquals(
      ['one' => ['issueId' => 1, 'title' => 'test1'], 'two' => ['issueId' => 2, 'title' => 'test2']],
      $this->fixture->marshal($issues)
    );
  }

  #[@test]
  public function marshal_static_member_excluded() {
    $o= newinstance(Value::class, [], '{
      public $name= "Test";
      public static $instance;
    }');
    $this->assertEquals(['name' => 'Test'], $this->fixture->marshal($o));
  }

  #[@test]
  public function marshal_money() {
    $this->fixture->addMarshaller('util.Money', self::$moneyMarshaller);
    $this->assertEquals(
      '6.10 USD',
      $this->fixture->marshal(new Money(6.10, Currency::$USD))
    );
  }

  #[@test]
  public function marshal_array_of_money() {
    $this->fixture->addMarshaller('util.Money', self::$moneyMarshaller);
    $this->assertEquals(
      ['6.10 USD'],
      $this->fixture->marshal([new Money(6.10, Currency::$USD)])
    );
  }

  #[@test]
  public function unmarshal_null() {
    $this->assertEquals(null, $this->fixture->unmarshal(Type::$VAR, null));
  }

  #[@test]
  public function unmarshal_null_as_string() {
    $this->assertEquals(null, $this->fixture->unmarshal(Primitive::$STRING, null));
  }

  #[@test]
  public function unmarshal_null_as_int() {
    $this->assertEquals(null, $this->fixture->unmarshal(Primitive::$INT, null));
  }

  #[@test]
  public function unmarshal_null_as_double() {
    $this->assertEquals(null, $this->fixture->unmarshal(Primitive::$DOUBLE, null));
  }

  #[@test]
  public function unmarshal_null_as_bool() {
    $this->assertEquals(null, $this->fixture->unmarshal(Primitive::$BOOL, null));
  }

  #[@test]
  public function unmarshal_string() {
    $this->assertEquals('Test', $this->fixture->unmarshal(Primitive::$STRING, 'Test'));
  }

  #[@test]
  public function unmarshal_int_as_string() {
    $this->assertEquals('1', $this->fixture->unmarshal(Primitive::$STRING, 1));
  }

  #[@test]
  public function unmarshal_double_as_string() {
    $this->assertEquals('1', $this->fixture->unmarshal(Primitive::$STRING, 1.0));
  }

  #[@test]
  public function unmarshal_bool_as_string() {
    $this->assertEquals('1', $this->fixture->unmarshal(Primitive::$STRING, true));
    $this->assertEquals('', $this->fixture->unmarshal(Primitive::$STRING, false));
  }

  #[@test]
  public function unmarshal_array_as_string() {
    $this->assertEquals('Test', $this->fixture->unmarshal(Primitive::$STRING, ['Test']));
  }

  #[@test]
  public function unmarshal_map_as_string() {
    $this->assertEquals('Test', $this->fixture->unmarshal(Primitive::$STRING, ['name' => 'Test']));
  }

  #[@test]
  public function unmarshal_int() {
    $this->assertEquals(1, $this->fixture->unmarshal(Primitive::$INT, 1));
  }

  #[@test]
  public function unmarshal_string_as_int() {
    $this->assertEquals(1, $this->fixture->unmarshal(Primitive::$INT, '1'));
  }

  #[@test]
  public function unmarshal_double_as_int() {
    $this->assertEquals(1, $this->fixture->unmarshal(Primitive::$INT, 1.0));
  }

  #[@test]
  public function unmarshal_bool_as_int() {
    $this->assertEquals(1, $this->fixture->unmarshal(Primitive::$INT, true));
    $this->assertEquals(0, $this->fixture->unmarshal(Primitive::$INT, false));
  }

  #[@test]
  public function unmarshal_array_as_int() {
    $this->assertEquals(1, $this->fixture->unmarshal(Primitive::$INT, [1]));
  }

  #[@test]
  public function unmarshal_map_as_int() {
    $this->assertEquals(1, $this->fixture->unmarshal(Primitive::$INT, ['one' => 1]));
  }

  #[@test]
  public function unmarshal_double() {
    $this->assertEquals(1.0, $this->fixture->unmarshal(Primitive::$DOUBLE, 1.0));
  }

  #[@test]
  public function unmarshal_string_as_double() {
    $this->assertEquals(1.0, $this->fixture->unmarshal(Primitive::$DOUBLE, '1.0'));
  }

  #[@test]
  public function unmarshal_int_as_double() {
    $this->assertEquals(1.0, $this->fixture->unmarshal(Primitive::$DOUBLE, 1));
  }

  #[@test]
  public function unmarshal_bool_as_double() {
    $this->assertEquals(1.0, $this->fixture->unmarshal(Primitive::$DOUBLE, true));
    $this->assertEquals(0.0, $this->fixture->unmarshal(Primitive::$DOUBLE, false));
  }

  #[@test]
  public function unmarshal_array_as_double() {
    $this->assertEquals(1.0, $this->fixture->unmarshal(Primitive::$DOUBLE, [1.0]));
  }

  #[@test]
  public function unmarshal_map_as_double() {
    $this->assertEquals(1.0, $this->fixture->unmarshal(Primitive::$DOUBLE, ['one' => 1.0]));
  }

  #[@test]
  public function unmarshal_bool() {
    $this->assertEquals(true, $this->fixture->unmarshal(Primitive::$BOOL, true));
    $this->assertEquals(false, $this->fixture->unmarshal(Primitive::$BOOL, false));
  }

  #[@test]
  public function unmarshal_int_as_bool() {
    $this->assertEquals(true, $this->fixture->unmarshal(Primitive::$BOOL, 1));
    $this->assertEquals(false, $this->fixture->unmarshal(Primitive::$BOOL, 0));
  }

  #[@test]
  public function unmarshal_double_as_bool() {
    $this->assertEquals(true, $this->fixture->unmarshal(Primitive::$BOOL, 1.0));
    $this->assertEquals(false, $this->fixture->unmarshal(Primitive::$BOOL, 0.0));
  }

  #[@test]
  public function unmarshal_string_as_bool() {
    $this->assertEquals(true, $this->fixture->unmarshal(Primitive::$BOOL, 'non-empty'));
    $this->assertEquals(false, $this->fixture->unmarshal(Primitive::$BOOL, ''));
  }

  #[@test]
  public function unmarshal_array_as_bool() {
    $this->assertEquals(true, $this->fixture->unmarshal(Primitive::$BOOL, [true]));
    $this->assertEquals(false, $this->fixture->unmarshal(Primitive::$BOOL, [false]));
  }

  #[@test]
  public function unmarshal_map_as_bool() {
    $this->assertEquals(true, $this->fixture->unmarshal(Primitive::$BOOL, ['one' => true]));
    $this->assertEquals(false, $this->fixture->unmarshal(Primitive::$BOOL, ['one' => false]));
  }

  #[@test]
  public function unmarshal_var_array() {
    $this->assertEquals(
      [1, 2, 3],
      $this->fixture->unmarshal(ArrayType::forName('var[]'), [1, 2, 3])
    );
  }

  #[@test]
  public function unmarshal_int_array() {
    $this->assertEquals(
      [1, 2, 3],
      $this->fixture->unmarshal(ArrayType::forName('int[]'), [1, '2', 3.0])
    );
  }

  #[@test]
  public function unmarshal_var_map() {
    $this->assertEquals(
      ['one' => 1, 'two' => 2, 'three' => 3],
      $this->fixture->unmarshal(MapType::forName('[:var]'), ['one' => 1, 'two' => 2, 'three' => 3])
    );
  }

  #[@test]
  public function unmarshal_int_map() {
    $this->assertEquals(
      ['one' => 1, 'two' => 2, 'three' => 3],
      $this->fixture->unmarshal(MapType::forName('[:int]'), ['one' => 1, 'two' => '2', 'three' => 3.0])
    );
  }

  #[@test]
  public function unmarshal_issue_with_field() {
    $issue= new IssueWithField(1, 'test');
    $this->assertEquals(
      $issue, 
      $this->fixture->unmarshal(typeof($issue), ['issue_id' => 1, 'title' => 'test'])
    );
  }

  #[@test]
  public function unmarshal_issue_with_underscore_field() {
    $issue= new IssueWithUnderscoreField(1, 'test');
    $this->assertEquals(
      $issue, 
      $this->fixture->unmarshal(typeof($issue), ['issue_id' => 1, 'title' => 'test'])
    );
  }

  #[@test]
  public function unmarshal_issue_with_setter() {
    $issue= new IssueWithSetter(1, 'test');
    $this->assertEquals(
      $issue, 
      $this->fixture->unmarshal(typeof($issue), ['issue_id' => 1, 'title' => 'test'])
    );
  }

  #[@test]
  public function unmarshal_issue_with_underscore_setter() {
    $issue= new IssueWithUnderscoreSetter(1, 'test');
    $this->assertEquals(
      $issue, 
      $this->fixture->unmarshal(typeof($issue), ['issue_id' => 1, 'title' => 'test'])
    );
  }

  #[@test]
  public function unmarshal_array_of_issues() {
    $issue1= new IssueWithField(1, 'test1');
    $issue2= new IssueWithField(2, 'test2');
    $this->assertEquals([$issue1, $issue2], $this->fixture->unmarshal(
      new ArrayType(nameof($issue1)),
      [['issue_id' => 1, 'title' => 'test1'], ['issue_id' => 2, 'title' => 'test2']]
    ));
  }

  #[@test]
  public function unmarshal_map_of_issues() {
    $issue1= new IssueWithField(1, 'test1');
    $issue2= new IssueWithField(2, 'test2');
    $this->assertEquals(['one' => $issue1, 'two' => $issue2], $this->fixture->unmarshal(
      MapType::forName('[:'.nameof($issue1).']'), 
      ['one' => ['issue_id' => 1, 'title' => 'test1'], 'two' => ['issue_id' => 2, 'title' => 'test2']]
    ));
  }

  #[@test]
  public function unmarshal_issues() {
    $issue1= new IssueWithField(1, 'test1');
    $issue2= new IssueWithField(2, 'test2');
    $this->assertEquals(new Issues($issue1, $issue2), $this->fixture->unmarshal(
      XPClass::forName('webservices.rest.unittest.Issues'),
      [['issue_id' => 1, 'title' => 'test1'], ['issue_id' => 2, 'title' => 'test2']]
    ));
  }

  #[@test]
  public function unmarshal_already_instance_of() {
    $issue= new IssueWithField(1, 'test1');
    $this->assertEquals($issue, $this->fixture->unmarshal(typeof($issue), $issue));
  }

  #[@test]
  public function unmarshal_no_constructor() {
    $class= ClassLoader::defineClass('RestConversionTest_NoConstructor', 'webservices.rest.unittest.ConstructorFixture', [], '{}');
    $this->assertEquals(4711, $this->fixture->unmarshal($class, ['id' => 4711])->id);
  }

  #[@test, @values([
  #  [4711],
  #  [[4711]],
  #  [[4711, 0815]],
  #  [['id' => 4711]],
  #  [['id' => 4711, 'name' => 'Test']]
  #])]
  public function unmarshal_static_valueof_method($input) {
    $class= ClassLoader::defineClass('RestConversionTest_StaticValueOf', Value::class, [], '{
      public $passed;
      public static function valueOf($args) { $self= new self(); $self->passed= $args; return $self; }
    }');
    $this->assertEquals($input, $this->fixture->unmarshal($class, $input)->passed);
  }

  #[@test, @values([
  #  [4711],
  #  [[4711]],
  #  [[4711, 0815]],
  #  [['id' => 4711]],
  #  [['id' => 4711, 'name' => 'Test']]
  #])]
  public function unmarshal_static_valueof_method_with_array_param($input) {
    $class= ClassLoader::defineClass('RestConversionTest_StaticValueOfWithArrayParam', Value::class, [], '{
      public $passed;
      public static function valueOf(array $args) { $self= new self(); $self->passed= $args; return $self; }
    }');
    $this->assertEquals((array)$input, $this->fixture->unmarshal($class, $input)->passed);
  }

  #[@test, @values([
  #  [4711],
  #  [[4711]],
  #  [[4711, 0815]],
  #  [['id' => 4711]],
  #  [['id' => 4711, 'name' => 'Test']]
  #])]
  public function unmarshal_static_valueof_method_with_int_param($input) {
    $class= ClassLoader::defineClass('RestConversionTest_StaticValueOfWithIntParam', 'webservices.rest.unittest.ConstructorFixture', [], '{
      protected function __construct($id) { $this->id= $id; }
      /** @param int $id */
      public static function valueOf($id) { return new self($id); }
    }');
    $this->assertEquals(4711, $this->fixture->unmarshal($class, $input)->id);
  }

  #[@test, @values([
  #  [[4711, 'Test']],
  #  [['id' => 4711, 'name' => 'Test']]
  #])]
  public function unmarshal_static_valueof_method_with_multiple_params($input) {
    $class= ClassLoader::defineClass('RestConversionTest_StaticValueOfWithMultipleParams', Value::class, [], '{
      public $passed;
      public static function valueOf($id, $name) { $self= new self(); $self->passed= [$id, $name]; return $self; }
    }');
    $this->assertEquals([4711, 'Test'], $this->fixture->unmarshal($class, $input)->passed);
  }

  #[@test]
  public function unmarshal_public_valueof_instance_method_not_invoked() {
    $class= ClassLoader::defineClass('RestConversionTest_PublicValueOf', 'webservices.rest.unittest.ConstructorFixture', [], '{
      public function valueOf($id) { throw new \lang\IllegalStateException("Should not reach this point!"); }
    }');
    $this->assertEquals(4711, $this->fixture->unmarshal($class, ['id' => 4711])->id);
  }

  #[@test]
  public function unmarshal_private_valueof_instance_method_not_invoked() {
    $class= ClassLoader::defineClass('RestConversionTest_PrivateValueOf', 'webservices.rest.unittest.ConstructorFixture', [], '{
      private static function valueOf($id) { throw new \lang\IllegalStateException("Should not reach this point!"); }
    }');
    $this->assertEquals(4711, $this->fixture->unmarshal($class, ['id' => 4711])->id);
  }

  #[@test]
  public function unmarshal_protected_valueof_instance_method_not_invoked() {
    $class= ClassLoader::defineClass('RestConversionTest_ProtectedValueOf', 'webservices.rest.unittest.ConstructorFixture', [], '{
      protected static function valueOf($id) { throw new \lang\IllegalStateException("Should not reach this point!"); }
    }');
    $this->assertEquals(4711, $this->fixture->unmarshal($class, ['id' => 4711])->id);
  }

  #[@test]
  public function unmarshal_date_iso_formatted() {
    $this->assertEquals(
      new Date('2009-04-12T20:44:55'), 
      $this->fixture->unmarshal(XPClass::forName('util.Date'), '2009-04-12T20:44:55')
    );
  }

  #[@test]
  public function unmarshal_constructor_not_used_with_complex_payload() {
    $class= ClassLoader::defineClass('RestConversionTest_ConstructorVsSetter', 'webservices.rest.unittest.ConstructorFixture', [], '{
      public $name;
      public function __construct() { 
        if (func_num_args() > 0) throw new \lang\IllegalStateException("Should not reach this point!");
      }
      public function withId($id) { $this->id= $id; return $this; }
      public function withName($name) { $this->name= $name; return $this; }
      public function equals($cmp) { return parent::equals($cmp) && $this->name === $cmp->name; }
      public function toString() { return parent::toString()."(name=\'".$this->name."\')"; }
    }');
    $this->assertEquals(
      $class->newInstance()->withId(4711)->withName('Test'),
      $this->fixture->unmarshal($class, ['id' => 4711, 'name' => 'Test'])
    );
  }

  #[@test]
  public function unmarshal_static_member_excluded() {
    $class= ClassLoader::defineClass('RestConversionTest_StaticMemberExcluded', Value::class, [], '{
      public $name;
      public static $instance= null;
    }');
    $instance= $this->fixture->unmarshal($class, ['name' => 'Test', 'instance' => 'Value']);
    $this->assertNull(typeof($instance)->getField('instance')->get(null));
  }

  #[@test]
  public function unmarshal_money() {
    $this->fixture->addMarshaller('util.Money', self::$moneyMarshaller);
    $this->assertEquals(
      new Money(6.10, Currency::$USD),
      $this->fixture->unmarshal(XPClass::forName('util.Money'), '6.10 USD')
    );
  }

  #[@test]
  public function unmarshal_array_of_money() {
    $this->fixture->addMarshaller('util.Money', self::$moneyMarshaller);
    $this->assertEquals(
      [new Money(6.10, Currency::$USD)],
      $this->fixture->unmarshal(ArrayType::forName('util.Money[]'), ['6.10 USD'])
    );
  }

  #[@test]
  public function marshal_works_recursively() {
    $this->fixture->addMarshaller('util.Money', self::$moneyMarshaller);
    $this->fixture->addMarshaller(self::$walletClass, self::$walletMarshaller);
    $this->assertEquals(
      ['0.25 USD'],
      $this->fixture->marshal(self::$walletClass->newInstance()->add(new Money(0.25, Currency::$USD)))
    );
  }

  #[@test]
  public function unmarshal_works_recursively() {
    $this->fixture->addMarshaller('util.Money', self::$moneyMarshaller);
    $this->fixture->addMarshaller(self::$walletClass, self::$walletMarshaller);
    $this->assertEquals(
      self::$walletClass->newInstance()->add(new Money(0.25, Currency::$USD)),
      $this->fixture->unmarshal(self::$walletClass, ['0.25 USD'])
    );
  }

  #[@test]
  public function unmarshal_enum() {
    $this->assertEquals(
      self::$enumClass->_reflect->getStaticPropertyValue('ELEMENT2'),
      $this->fixture->unmarshal(self::$enumClass, 'ELEMENT2')
    );
  }

  #[@test]
  public function marshal_enum() {
    $this->assertEquals(
      'ELEMENT2',
      $this->fixture->marshal(self::$enumClass->_reflect->getStaticPropertyValue('ELEMENT2'))
    );
  }

}
