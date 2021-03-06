<?php

namespace OpenConext\Value\Saml;

use OpenConext\Value\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;

class EntitySetTest extends TestCase
{
    /**
     * @test
     * @group entity
     *
     * @dataProvider unequalSets
     *
     * @param array $firstSet
     * @param array $secondSet
     */
    public function set_with_different_elements_are_not_considered_equal(array $firstSet, array $secondSet)
    {
        $base  = new EntitySet($firstSet);
        $other = new EntitySet($secondSet);

        $this->assertFalse($base->equals($other));
    }

    public function unequalSets()
    {
        return array(
            'Different elements' => array(
                array(new Entity(new EntityId('a'), EntityType::SP()), new Entity(new EntityId('a'), EntityType::IdP())),
                array(new Entity(new EntityId('b'), EntityType::IdP())),
            ),
            'First set contains second set' => array(
                array(new Entity(new EntityId('a'), EntityType::SP()), new Entity(new EntityId('a'), EntityType::IdP())),
                array(new Entity(new EntityId('a'), EntityType::SP())),
            ),
            'Different EntityType' => array(
                array(new Entity(new EntityId('a'), EntityType::IdP())),
                array(new Entity(new EntityId('a'), EntityType::SP())),
            ),
            'Second set is empty' => array(
                array(new Entity(new EntityId('a'), EntityType::IdP())),
                array(),
            ),
            'First set is empty' => array(
                array(),
                array(new Entity(new EntityId('a'), EntityType::IdP())),
            ),
        );
    }

    /**
     * @test
     * @group entity
     *
     * @dataProvider equalSets
     *
     * @param array $firstSet
     * @param array $secondSet
     */
    public function set_with_equal_elements_are_considered_equal(array $firstSet, array $secondSet)
    {
        $base  = new EntitySet($firstSet);
        $other = new EntitySet($secondSet);

        $this->assertTrue($base->equals($other));
    }

    public function equalSets()
    {
        return array(
            'Same Entities' => array(
                array(new Entity(new EntityId('a'), EntityType::IdP())),
                array(new Entity(new EntityId('a'), EntityType::IdP())),
            ),
            'Both emtpy' => array(
                array(),
                array(),
            ),
            'Same Entities due to deduplication in the first set' => array(
                array(new Entity(new EntityId('a'), EntityType::SP()), new Entity(new EntityId('a'), EntityType::SP())),
                array(new Entity(new EntityId('a'), EntityType::SP())),
            ),
            'Same Entities, different Sequence' => array(
                array(new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('b'), EntityType::SP())),
                array(new Entity(new EntityId('b'), EntityType::SP()), new Entity(new EntityId('a'), EntityType::IdP())),
            ),
        );
    }

    /**
     * @test
     * @group entity
     */
    public function elements_in_a_set_can_be_tested_for_presence_based_on_equality()
    {
        $entityInSetOne = new Entity(new EntityId('RUG'), EntityType::SP());
        $entityInSetTwo = new Entity(new EntityId('HU'), EntityType::IdP());
        $entityNotInSet = new Entity(new EntityId('UM'), EntityType::IdP());

        $entitySet = new EntitySet(array($entityInSetOne, $entityInSetTwo));

        $this->assertTrue($entitySet->contains($entityInSetOne));
        $this->assertTrue($entitySet->contains(new Entity(new EntityId('HU'), EntityType::IdP())));
        $this->assertFalse($entitySet->contains($entityNotInSet));
    }

    /**
     * @test
     * @group entity
     */
    public function entity_set_deduplicates_equal_elements()
    {
        $entity            = new Entity(new EntityId('RUG'), EntityType::SP());
        $differentInstance = new Entity(new EntityId('RUG'), EntityType::SP());

        $entitySet = new EntitySet(array($entity, $entity, $differentInstance));

        $this->assertCount(1, $entitySet);
    }

    /**
     * @test
     * @group entity
     */
    public function an_entity_set_can_be_iterated_over()
    {
        $entityInSetOne = new Entity(new EntityId('RUG'), EntityType::SP());
        $entityInSetTwo = new Entity(new EntityId('HU'), EntityType::IdP());

        $entitySet = new EntitySet(array($entityInSetOne, $entityInSetTwo));

        $unknownEntityFound = false;
        $entityOneSeen = false;
        $entityTwoSeen = false;

        foreach ($entitySet as $entity) {
            if (!$entityOneSeen && $entity === $entityInSetOne) {
                $entityOneSeen = true;
            } elseif (!$entityTwoSeen && $entity === $entityInSetTwo) {
                $entityTwoSeen = true;
            } else {
                $unknownEntityFound = true;
            }
        }

        $this->assertFalse($unknownEntityFound, 'Unknown entity discovered when iterating over set');
        $this->assertTrue($entityOneSeen, 'Expected to see defined entityInSetOne when iterating over set');
        $this->assertTrue($entityTwoSeen, 'Expected to see defined entityInSetTwo when iterating over set');
    }

    /**
     * @test
     * @group entity
     */
    public function deserializing_a_serialized_entity_set_results_in_an_equal_value_object()
    {
        $entityInSetOne = new Entity(new EntityId('RUG'), EntityType::SP());
        $entityInSetTwo = new Entity(new EntityId('HU'), EntityType::IdP());

        $original     = new EntitySet(array($entityInSetOne, $entityInSetTwo));
        $deserialized = EntitySet::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group entity
     *
     * @dataProvider \OpenConext\Value\TestDataProvider::notArray
     * @expectedException InvalidArgumentException
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_an_array($notArray)
    {
        EntitySet::deserialize($notArray);
    }

    /**
     * @test
     * @group entity
     */
    public function an_entity_set_can_be_cast_to_a_known_format_string()
    {
        $entityOne = new Entity(new EntityId('RUG'), EntityType::SP());
        $entityTwo = new Entity(new EntityId('HU'), EntityType::IdP());
        $entities  = array($entityOne, $entityTwo);

        $entitySet = new EntitySet($entities);

        $this->assertEquals(sprintf('EntitySet["%s"]', implode('", "', $entities)), (string) $entitySet);
    }
}
