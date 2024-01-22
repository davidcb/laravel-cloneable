<?php

namespace Davidcb\LaravelCloneable\Test;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CloneableTest extends TestCase
{
    /* public function testDuplicate() {

		m::mock('alias:App', [
			'make' => m::mock('Bkwld\Cloner\Cloner', [
				'duplicate' => m::mock('Bkwld\Cloner\Stubs\Article'),
			])
		]);

		$article = new Article;
		$clone = $article->duplicate();
		$this->assertInstanceOf('Bkwld\Cloner\Stubs\Article', $clone);
	}*/

    /** @test */
    public function it_clones_a_model()
    {
        $dummy = Dummy::first();
        $clone = $dummy->duplicate();

        $this->assertInstanceOf(Dummy::class, $clone);
        $this->assertEquals('Title 1', $clone->title);
        $this->assertEquals('Description 1', $clone->description);
        $this->assertCount(2, Dummy::all());
    }

    /** @test */
    public function it_adds_a_cloneable_relation()
    {
        $dummy = Dummy::first();
        $dummy->addCloneableRelation('related');
        $this->assertEquals(['related'], $dummy->getCloneableRelations());
    }

    /** @test */
    public function it_adds_a_cloneable_relation_only_once()
    {
        $dummy = Dummy::first();
        $dummy->addCloneableRelation('related');
        $dummy->addCloneableRelation('related');
        $this->assertEquals(['related'], $dummy->getCloneableRelations());
    }

    /** @test */
    public function it_clones_a_model_with_relations()
    {
        $dummy = Dummy::first();
        $dummy->addCloneableRelation('related');
        $clone = $dummy->duplicate();

        $this->assertInstanceOf(DummyRelated::class, $clone->related->first());
        $this->assertCount(2, Dummy::all());
    }

    /** @test */
    /* public function it_clones_a_model_with_files()
    {
        config()->set('filesystems.disks.media', [
            'driver' => 'local',
            'root' => __DIR__ . '/../temp',
        ]);

        config()->set('medialibrary.default_filesystem', 'media');

        $dummy = Dummy::first();
        $dummy->addMediaFromUrl('https://picsum.photos/200/300')->toMediaCollection('image');
        $clone = $dummy->duplicate();

        $this->assertInstanceOf(Media::class, $clone->getFirstMedia('image'));
        $this->assertCount(2, Dummy::all());
    } */
}
