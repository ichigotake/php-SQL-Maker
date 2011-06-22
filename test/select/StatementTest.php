<?php
ini_set('include_path',
        ini_get('include_path')
        .PATH_SEPARATOR
        .dirname(__FILE__).'/../../lib');

require_once('SQL/Maker/Select.php');


class StatementTest extends PHPUnit_Framework_TestCase {

    public function testPrefixQuoteCharNameSepSimple() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT *\nFROM `foo`", $stmt->asSql());
    }

    public function testPrefixQuoteCharNameSepSQL_CALC_FOUND_ROWS() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->prefix = 'SELECT SQL_CALC_FOUND_ROWS ';
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT SQL_CALC_FOUND_ROWS *\nFROM `foo`", $stmt->asSql());
    }

    public function testPrefixQuoteCharNameSepNewlineSimple() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT * FROM `foo`", $stmt->asSql());
    }

    public function testPrefixQuoteCharNameSepNewlineSQL_CALC_FOUND_ROWS() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->prefix = 'SELECT SQL_CALC_FOUND_ROWS ';
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT SQL_CALC_FOUND_ROWS * FROM `foo`", $stmt->asSql());
    }


    public function testFromQuoteCharNameSepSingle() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo');
        $this->assertEquals('FROM `foo`', $stmt->asSql());
    }

    public function testFromQuoteCharNameSepMulti() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addFrom( 'bar' );
        $this->assertEquals("FROM `foo`, `bar`", $stmt->asSql());
    }

    public function testFromQuoteCharNameSepMultiPlusAlias() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo', 'f');
        $stmt->addFrom('bar', 'b');
        $this->assertEquals("FROM `foo` `f`, `bar` `b`", $stmt->asSql());
    }


    public function testJoinQuoteCharNameSepInnerJoin() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type' => 'inner',
                             'table' => 'baz',
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz`", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepInnerJoinWithCondition() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'condition' => 'foo.baz_id = baz.baz_id',
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz` ON foo.baz_id = baz.baz_id", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepFromAndInnerJoinWithCondition() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'bar' );
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'condition' => 'foo.baz_id = baz.baz_id'
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz` ON foo.baz_id = baz.baz_id, `bar`", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepTestCaseForBugFoundWhereAddJoinIsCalledTwice() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'alias'     => 'b1',
                             'condition' => 'foo.baz_id = b1.baz_id AND b1.quux_id = 1'
                             )
                       );
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'left',
                             'table'     => 'baz',
                             'alias'     => 'b2',
                             'condition' => 'foo.baz_id = b2.baz_id AND b2.quux_id = 2'
                             )
                       );

        $this->assertEquals("FROM `foo` INNER JOIN `baz` `b1` ON foo.baz_id = b1.baz_id AND b1.quux_id = 1 LEFT JOIN `baz` `b2` ON foo.baz_id = b2.baz_id AND b2.quux_id = 2", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepTestCaseAddingAnotherTableOntoTheWholeMess() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'alias'     => 'b1',
                             'condition' => 'foo.baz_id = b1.baz_id AND b1.quux_id = 1'
                             )
                       );
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'left',
                             'table'     => 'baz',
                             'alias'     => 'b2',
                             'condition' => 'foo.baz_id = b2.baz_id AND b2.quux_id = 2'
                             )
                       );
        $stmt->addJoin(
                       'quux',
                       array(
                             'type'      => 'inner',
                             'table'     => 'foo',
                             'alias'     => 'f1',
                             'condition'  => 'f1.quux_id = quux.q_id'
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz` `b1` ON foo.baz_id = b1.baz_id AND b1.quux_id = 1 LEFT JOIN `baz` `b2` ON foo.baz_id = b2.baz_id AND b2.quux_id = 2 INNER JOIN `foo` `f1` ON f1.quux_id = quux.q_id", $stmt->asSql());
    }

    public function ns($args) {
        return new SQL_Maker_Select($args);
    }

}
