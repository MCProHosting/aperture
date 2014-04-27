<?php


class SnapshotTest extends PHPUnit_Framework_TestCase
{
    public function testTakes()
    {
        $db = Mockery::mock('\Illuminate\Database\DatabaseManager');
        $db->shouldReceive('connection')->with('connection')->andReturn(Mockery::self());
        $db->shouldReceive('table')->with('table')->andReturn(Mockery::self());
        $db->shouldReceive('chunk')->with(500, Mockery::type('callable'))->andReturn('it worked!');

        $cls = new \Mcprohosting\Aperture\Snapshot($db);
        $cls->handle = fopen('php://memory', 'w');

        $this->assertEquals('it worked!', $cls->take('connection', 'table', 500));
    }

    public function testRestores()
    {
        $db = Mockery::mock('\Illuminate\Database\DatabaseManager');
        $db->shouldReceive('connection')->with('connection')->andReturn(Mockery::self());
        $db->shouldReceive('table')->with('table')->andReturn(Mockery::self());
        $db->shouldReceive('truncate');
        $db->shouldReceive('insert')->with(array(array('col1', 'col2'), array('col1', 'col2'), array('col1', 'col2')));
        $db->shouldReceive('insert')->with(array(array('col1', 'col2'), array('col1', 'col2')));
        $db->shouldReceive('chunk')->with(500, Mockery::type('callable'))->andReturn('it worked!');

        $cls = new \Mcprohosting\Aperture\Snapshot($db);
        $cls->handle = fopen('php://memory', 'w+');

        fwrite($cls->handle, "col1,col2\n");
        fwrite($cls->handle, "col1,col2\n");
        fwrite($cls->handle, "col1,col2\n");
        fwrite($cls->handle, "col1,col2\n");
        fwrite($cls->handle, "col1,col2\n");
        rewind($cls->handle);

        $cls->restore('connection', 'table', 3);
    }
} 
