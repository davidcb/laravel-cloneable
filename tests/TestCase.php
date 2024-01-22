<?php

namespace Davidcb\LaravelCloneable\Test;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('dummies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('dummies_related', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('dummy_id')->nullable();
            $table->timestamps();
        });

        $dummy = Dummy::create([
            'title' => 'Title 1',
            'description' => 'Description 1',
        ]);

        collect(range(1, 3))->each(function (int $i) use ($dummy) {
            DummyRelated::create([
                'title' => 'Related title ' . $i,
                'description' => 'Related description' . $i,
                'dummy_id' => $dummy->id,
            ]);
        });
    }
}
