<?php

namespace MediaTech\Query\Tests\Unit\Query;


use MediaTech\Query\Expression\Field;
use MediaTech\Query\Factory;
use MediaTech\Query\Query\Filter\FilterGroup;
use MediaTech\Query\Query\Select;
use MediaTech\Query\Tests\Unit\BaseTestCase;

class SelectTest extends BaseTestCase
{
    public function testConstructor()
    {
        $pdo = $this->createPDOMock();
        $table = 'foo.bar';

        $query = new Select($pdo, $table);

        $this->assertAttributeInstanceOf(\PDO::class, 'pdo', $query);
        $this->assertAttributeInstanceOf(FilterGroup::class, 'filters', $query);
        $this->assertAttributeEquals('foo.bar', 'table', $query);
        $this->assertAttributeEquals(Select::DEFAULT_TABLE_ALIAS, 'alias', $query);
        $this->assertAttributeEquals([], 'columns', $query);
        $this->assertAttributeEquals([], 'joins', $query);
        $this->assertAttributeEquals([], 'groupBy', $query);
        $this->assertAttributeEquals([], 'orderBy', $query);
        $this->assertAttributeEquals([], 'with', $query);
        $this->assertAttributeEquals(null, 'distinct', $query);
        $this->assertAttributeEquals(null, 'having', $query);
        $this->assertAttributeEquals(null, 'limit', $query);
        $this->assertAttributeEquals(null, 'offset', $query);

        $query = new Select($pdo, $table, 'test');

        $this->assertAttributeEquals('test', 'alias', $query);
    }

    public function testAlias()
    {
        $query = $this->createQuery();
        $query->alias('s1');

        $this->assertAttributeEquals('s1', 'alias', $query);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Column list is empty
     */
    public function testColumnsWithEmptyList()
    {
        $query = $this->createQuery();
        $query->columns([]);
    }

    public function testColumns()
    {
        $query = $this->createQuery();

        $columnsList = ['foo', 'bar'];
        $query->columns($columnsList);

        $this->assertAttributeEquals($columnsList, 'columns', $query);

        $columns = 'foo ,  bar ';
        $query->columns($columns);

        $this->assertAttributeEquals($columnsList, 'columns', $query);
    }

    public function testDistinct()
    {
        $query = $this->createQuery();
        $query->distinct();

        $this->assertAttributeEquals(true, 'distinct', $query);

        $query->distinct(false);

        $this->assertAttributeEquals(false, 'distinct', $query);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Table has already joined
     */
    public function testJoinWithAlreadyJoinedTable()
    {
        $query = $this->createQuery();
        $query->join('test', 't2', 't2.id = t1.user_id');
        $query->join('test', 't2', 't2.id = t1.user_type');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Alias is already in use
     */
    public function testJoinWithDuplicatedAlias()
    {
        $query = $this->createQuery();
        $query->join('test', 't2', 't2.id = t1.user_id');
        $query->join('any', 't2', 't2.id = t1.user_type');
    }

    public function testInnerJoin()
    {
        $query = $this->createQuery();
        $query->innerJoin('test', 't2', 't2.id = t1.user_id');

        $hash = hash('crc32', 'test_t2');

        $this->assertAttributeEquals([
            $hash => [
                'type' => 'inner',
                'table' => 'test',
                'alias' => 't2',
                'condition' => 't2.id = t1.user_id',
            ]
        ], 'joins', $query);
    }

    public function testLeftJoin()
    {
        $query = $this->createQuery();
        $query->leftJoin('test', 't2', 't2.id = t1.user_id');

        $hash = hash('crc32', 'test_t2');

        $this->assertAttributeEquals([
            $hash => [
                'type' => 'left',
                'table' => 'test',
                'alias' => 't2',
                'condition' => 't2.id = t1.user_id',
            ]
        ], 'joins', $query);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only select query can be used
     */
    public function testWithWithInvalidQuery()
    {
        $factory = Factory::create($this->createPDOMock());

        $cte = [
            'regional_sales' => $factory
                ->select('orders')
                ->columns(['region', Field::create('SUM(amount)', 'total_sales')])
                ->groupBy(['region']),
            'top_regions' => new \stdClass()
        ];

        $query = $this->createQuery();
        $query->with($cte);
    }

    public function testWith()
    {
        $factory = Factory::create($this->createPDOMock());

        $cte = [
            'regional_sales' => $factory
                ->select('orders')
                ->columns(['region', Field::create('SUM(amount)', 'total_sales')])
                ->groupBy(['region']),
            'top_regions' => $factory
                ->select('regional_sales')
                ->columns(['region'])
                ->andMore('total_sales', 1000)
        ];

        $query = $this->createQuery();
        $query->with($cte);

        $this->assertAttributeEquals($cte, 'with', $query);
    }

    public function testGroupBy()
    {
        $query = $this->createQuery();
        $query->groupBy(['test']);

        $this->assertAttributeEquals(['test'], 'groupBy', $query);
    }

    public function testOrderBy()
    {
        $query = $this->createQuery();
        $query->orderBy(['test', 'field desc']);

        $this->assertAttributeEquals(['test', 'field desc'], 'orderBy', $query);
    }

    public function testHaving()
    {
        $query = $this->createQuery();
        $query->having('total_amount >= 1500');

        $this->assertAttributeEquals('total_amount >= 1500', 'having', $query);
    }

    public function testLimit()
    {
        $query = $this->createQuery();
        $query->limit(500);

        $this->assertAttributeEquals(500, 'limit', $query);
    }

    public function testOffset()
    {
        $query = $this->createQuery();
        $query->offset(20);

        $this->assertAttributeEquals(20, 'offset', $query);
    }

    public function testRange()
    {
        $query = $this->createQuery();
        $query->range(100, 500);

        $this->assertAttributeEquals(100, 'offset', $query);
        $this->assertAttributeEquals(500, 'limit', $query);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Query build error: using having without group by
     */
    public function testBuildQueryWithHavingWithoutGroupBy()
    {
        $query = $this->createQuery();
        $query
            ->having('total_amount >= 1500')
            ->__toString();
    }

    public function testBuild()
    {
        $factory = Factory::create($this->createPDOMock());

        $cte = [
            'regional_sales' => $factory
                ->select('orders')
                ->columns(['region', Field::create('SUM(amount)', 'total_sales')])
                ->groupBy(['region']),
            'top_regions' => $factory
                ->select('regional_sales')
                ->columns(['region'])
                ->andMore('total_sales', 1000)
        ];

        $query = $factory
            ->select('foo.bar')
            ->with($cte)
            ->columns(['field1', 'field2'])
            ->join('test', 't2', 't2.id = t1.user_id')
            ->andIn('field1', [3, 7, 9])
            ->orIsNull('field2')
            ->having('total_amount >= 1500')
            ->groupBy('region')
            ->orderBy(['region desc'])
            ->limit(1000)
            ->offset(150);

        $this->assertEquals('WITH regional_sales AS (SELECT region,SUM(amount) AS total_sales FROM orders t1 GROUP BY region), top_regions AS (SELECT region FROM regional_sales t1 WHERE total_sales > 1000) SELECT field1,field2 FROM foo.bar t1 INNER JOIN test t2 ON t2.id = t1.user_id WHERE field1 IN (3,7,9) OR field2 IS NULL GROUP BY region HAVING total_amount >= 1500 ORDER BY region desc LIMIT 1000 OFFSET 150', (string)$query);
    }

    public function testBuildConditionGroups()
    {
        $query = $this->createQuery();

        $query
            ->beginGroup()
            ->equals('foo', 1)
            ->orIsNull('bar')
            ->endGroup();

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE ( foo = 1 OR bar IS NULL )', (string)$query);

        $query = $this->createQuery();

        $query
            ->equals('test', 5)
            ->orGroup()
            ->equals('foo', 1)
            ->orIsNull('bar')
            ->endGroup();

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE test = 5 OR ( foo = 1 OR bar IS NULL )', (string)$query);
    }

    public function testBuildConditionsWithEqualFilters()
    {
        $query = $this->createQuery();

        $query
            ->equals('foo', 1)
            ->andEquals('bar', false)
            ->orEquals('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo = 1 AND bar = false OR baz = 10', (string)$query);
    }

    public function testBuildConditionsWithNotEqualFilters()
    {
        $query = $this->createQuery();

        $query
            ->notEquals('foo', 1)
            ->andNotEquals('bar', false)
            ->orNotEquals('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo != 1 AND bar != false OR baz != 10', (string)$query);
    }

    public function testBuildConditionsWithMoreFilters()
    {
        $query = $this->createQuery();

        $query
            ->more('foo', 1)
            ->andMore('bar', 5)
            ->orMore('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo > 1 AND bar > 5 OR baz > 10', (string)$query);
    }

    public function testBuildConditionsWithMoreOrEqualsFilters()
    {
        $query = $this->createQuery();

        $query
            ->moreOrEquals('foo', 1)
            ->andMoreOrEquals('bar', 5)
            ->orMoreOrEquals('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo >= 1 AND bar >= 5 OR baz >= 10', (string)$query);
    }

    public function testBuildConditionsWithLessFilters()
    {
        $query = $this->createQuery();

        $query
            ->less('foo', 1)
            ->andLess('bar', 5)
            ->orLess('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo < 1 AND bar < 5 OR baz < 10', (string)$query);
    }

    public function testBuildConditionsWithLessOrEqualsFilters()
    {
        $query = $this->createQuery();

        $query
            ->lessOrEquals('foo', 1)
            ->andLessOrEquals('bar', 5)
            ->orLessOrEquals('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo <= 1 AND bar <= 5 OR baz <= 10', (string)$query);
    }

    public function testBuildConditionsWithRangeFilters()
    {
        $query = $this->createQuery();

        $query
            ->between('foo', [1,2])
            ->andBetween('bar', [5,6])
            ->orBetween('baz', [10,20]);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo BETWEEN 1 AND 2 AND bar BETWEEN 5 AND 6 OR baz BETWEEN 10 AND 20', (string)$query);
    }

    public function testBuildConditionsWithNullFilters()
    {
        $query = $this->createQuery();

        $query
            ->isNull('foo')
            ->andIsNull('bar')
            ->orIsNull('baz');

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo IS NULL AND bar IS NULL OR baz IS NULL', (string)$query);
    }

    public function testBuildConditionsWithNotNullFilters()
    {
        $query = $this->createQuery();

        $query
            ->isNotNull('foo')
            ->andIsNotNull('bar')
            ->orIsNotNull('baz');

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo IS NOT NULL AND bar IS NOT NULL OR baz IS NOT NULL', (string)$query);
    }

    public function testBuildConditionsWithInFilters()
    {
        $query = $this->createQuery();

        $query
            ->in('foo', [1,2])
            ->andIn('bar', [5,6])
            ->orIn('baz', [10,20]);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo IN (1,2) AND bar IN (5,6) OR baz IN (10,20)', (string)$query);
    }

    public function testBuildConditionsWithNotInFilters()
    {
        $query = $this->createQuery();

        $query
            ->notIn('foo', [1,2])
            ->andNotIn('bar', [5,6])
            ->orNotIn('baz', [10,20]);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo NOT IN (1,2) AND bar NOT IN (5,6) OR baz NOT IN (10,20)', (string)$query);
    }

    public function testBuildConditionsWithInArrayFilters()
    {
        $query = $this->createQuery();

        $query
            ->inArray('foo', 1)
            ->andInArray('bar', 5)
            ->orInArray('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE 1 = ANY(foo) AND 5 = ANY(bar) OR 10 = ANY(baz)', (string)$query);
    }

    public function testBuildConditionsWithNotInArrayFilters()
    {
        $query = $this->createQuery();

        $query
            ->notInArray('foo', 1)
            ->andNotInArray('bar', 5)
            ->orNotInArray('baz', 10);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE 1 != ANY(foo) AND 5 != ANY(bar) OR 10 != ANY(baz)', (string)$query);
    }

    public function testBuildConditionsWithArrayContainsFilters()
    {
        $query = $this->createQuery();

        $query
            ->arrayContains('foo', [1,2])
            ->andArrayContains('bar', [5,6])
            ->orArrayContains('baz', [10,20]);

        $this->assertEquals('SELECT * FROM foo.bar t1 WHERE foo @> ARRAY[1,2]::INTEGER[] AND bar @> ARRAY[5,6]::INTEGER[] OR baz @> ARRAY[10,20]::INTEGER[]', (string)$query);
    }

    public function testExecute()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT * FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 10';
        $statement = new \PDOStatement();

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(10);

        $this->assertInstanceOf(Select::class, $query->execute());
        $this->assertAttributeInstanceOf(\PDOStatement::class, 'statement', $query);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Data fetch error: query must be executed before fetch data
     */
    public function testFetchAllObjectBeforeExecute()
    {
        $query = $this->createQuery();
        $query->fetchAllObject(\stdClass::class);
    }

    public function testFetchAllObject()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT * FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 10';

        $data = [
            new \stdClass(),
            new \stdClass(),
        ];

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchAll')
            ->with(\PDO::FETCH_CLASS, \stdClass::class)
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(10)
            ->execute();

        $this->assertEquals($data, $query->fetchAllObject(\stdClass::class));
    }

    public function testFetchCallback()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT * FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 10';

        $data = [
            new \stdClass(),
            new \stdClass(),
        ];
        $callback = function () {};

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchAll')
            ->with(\PDO::FETCH_FUNC, $callback)
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(10)
            ->execute();

        $this->assertEquals($data, $query->fetchCallback($callback));
    }

    public function testFetchAllAssoc()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT * FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 10';

        $data = [
            ['foo' => 1, 'bar' => 2],
            ['foo' => 3, 'bar' => 4],
        ];

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(10)
            ->execute();

        $this->assertEquals($data, $query->fetchAllAssoc());
    }

    public function testFetchKeyPair()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT field1,field2 FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 10';

        $data = [
            [1 => 'foo'],
            [2 => 'bar'],
        ];

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchAll')
            ->with(\PDO::FETCH_KEY_PAIR)
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->columns(['field1', 'field2'])
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(10)
            ->execute();

        $this->assertEquals($data, $query->fetchKeyValue());
    }

    public function testFetchAllColumn()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT field1 FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 10';

        $data = [1, 2];

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchAll')
            ->with(\PDO::FETCH_COLUMN)
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->columns('field1')
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(10)
            ->execute();

        $this->assertEquals($data, $query->fetchAllColumn());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Data fetch error: query must be executed before fetch data
     */
    public function testFetchOneObjectBeforeExecute()
    {
        $query = $this->createQuery();
        $query->fetchOneObject(\stdClass::class);
    }

    public function testFetchOneObject()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT * FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 1';

        $data = new \stdClass();

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchObject')
            ->with(\stdClass::class)
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(1)
            ->execute();

        $this->assertEquals($data, $query->fetchOneObject(\stdClass::class));
    }

    public function testFetchOneAssoc()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT * FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 1';

        $data = ['foo' => 1, 'bar' => 2];

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetch')
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(1)
            ->execute();

        $this->assertEquals($data, $query->fetchOneAssoc());
    }

    public function testFetchColumn()
    {
        $pdo = $this->createPDOMock();

        $query = 'SELECT field1 FROM foo.bar t1 WHERE field1 IN (3,7,9) ORDER BY field2 desc LIMIT 1';

        $data = 1;

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->method('fetchColumn')
            ->willReturn($data);

        $pdo
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $query = new Select($pdo, 'foo.bar');

        $query
            ->columns('field1')
            ->andIn('field1', [3, 7, 9])
            ->orderBy(['field2 desc'])
            ->limit(1)
            ->execute();

        $this->assertEquals($data, $query->fetchColumn());
    }

    private function createQuery()
    {
        $pdo = $this->createPDOMock();
        $table = 'foo.bar';

        return new Select($pdo, $table);
    }
}