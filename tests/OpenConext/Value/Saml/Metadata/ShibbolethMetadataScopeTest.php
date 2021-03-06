<?php

namespace OpenConext\Value\Saml\Metadata;

use OpenConext\Value\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class ShibbolethMetadataScopeTest extends UnitTest
{
    /**
     * @test
     * @group metadata
     *
     * @expectedException \OpenConext\Value\Exception\InvalidArgumentException
     * @dataProvider \OpenConext\Value\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $literal
     */
    public function a_literal_scope_factory_method_only_accepts_a_non_empty_string($literal)
    {
        ShibbolethMetadataScope::literal($literal);
    }

    /**
     * @test
     * @group metadata
     *
     * @expectedException \OpenConext\Value\Exception\InvalidArgumentException
     * @dataProvider \OpenConext\Value\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $regexp
     */
    public function the_regexp_scope_factory_method_only_accepts_a_non_empty_string($regexp)
    {
        ShibbolethMetadataScope::regexp($regexp);
    }

    /**
     * @test
     * @group metadata
     *
     * @dataProvider \OpenConext\Value\TestDataProvider::notStringOrEmptyString
     * @expectedException InvalidArgumentException
     *
     * @param mixed $invalidValue
     */
    public function a_scope_can_only_be_created_using_a_non_empty_string($invalidValue)
    {
        new ShibbolethMetadataScope($invalidValue);
    }

    /**
     * @test
     * @group metadata
     *
     * @dataProvider \OpenConext\Value\TestDataProvider::notBoolean
     * @expectedException InvalidArgumentException
     *
     * @param mixed $notBoolean
     */
    public function a_regexp_scope_can_only_be_created_with_a_boolean_argument($notBoolean)
    {
        new ShibbolethMetadataScope('/scope/', $notBoolean);
    }

    /**
     * @test
     * @group        metadata
     *
     * @dataProvider \OpenConext\Value\TestDataProvider::notString
     * @expectedException InvalidArgumentException
     *
     * @param mixed $notString
     */
    public function the_value_to_verify_if_allowed_must_be_a_string($notString)
    {
        $scope = new ShibbolethMetadataScope('foo');
        $scope->allows($notString);
    }

    /**
     * @test
     * @group metadata
     */
    public function a_literal_scopes_allows_only_exact_matches()
    {
        $scope = new ShibbolethMetadataScope('abcde');

        $this->assertTrue($scope->allows('abcde'));
        $this->assertFalse($scope->allows('abcd'));
        $this->assertFalse($scope->allows('abcdef'));
    }

    /**
     * @test
     * @group metadata
     */
    public function a_regex_scope_allows_matches()
    {
        $scope = new ShibbolethMetadataScope('/a{3,4}/i', true);

        $this->assertTrue($scope->allows('aaa'));
        $this->assertTrue($scope->allows('aAAa'));
        $this->assertFalse($scope->allows('aaba'));
        $this->assertFalse($scope->allows('1231'));
    }

    /**
     * @test
     * @group metadata
     */
    public function a_literal_scope_is_not_equal_to_a_regexp_scope()
    {
        $literalScope = new ShibbolethMetadataScope('/notEqual/');
        $regexpScope  = new ShibbolethMetadataScope('/notEqual/', true);

        $this->assertFalse($literalScope->equals($regexpScope));
    }
    /**
     * @test
     * @group metadata
     */
    public function literal_scopes_are_compared_based_on_the_literal()
    {
        $base      = new ShibbolethMetadataScope('literal');
        $theSame   = new ShibbolethMetadataScope('literal');
        $different = new ShibbolethMetadataScope('different');

        $this->assertTrue(
            $base->equals($theSame),
            'Two literal scopes must be the same if they are created with the same literal'
        );

        $this->assertFalse(
            $base->equals($different),
            'Two literal scopes must be different if they are created with different literals'
        );
    }

    /**
     * @test
     * @group metadata
     */
    public function regex_scopes_are_compared_based_on_the_regexp_given()
    {
        $base      = new ShibbolethMetadataScope('/[0-9a-z]{8}/', true);
        $theSame   = new ShibbolethMetadataScope('/[0-9a-z]{8}/', true);
        $different = new ShibbolethMetadataScope('/different/', true);

        $this->assertTrue(
            $base->equals($theSame),
            'Two regexp scopes must be the same if they are created with the same regexp'
        );

        $this->assertFalse(
            $base->equals($different),
            'Two literal scopes must be different if they are created with different literals'
        );
    }


    /**
     * @test
     * @group metadata
     */
    public function deserializing_a_serialized_shibmd_scope_results_in_an_equal_value_object()
    {
        $literal = new ShibbolethMetadataScope('foobar');
        $regexp  = new ShibbolethMetadataScope('/abc/i', true);

        $deserializedLiteral = ShibbolethMetadataScope::deserialize($literal->serialize());
        $deserializedRegexp  = ShibbolethMetadataScope::deserialize($regexp->serialize());

        $this->assertTrue(
            $deserializedLiteral->equals($literal),
            'Deserialized literal scope must equal the literal scope that was serialized'
        );
        $this->assertTrue(
            $deserializedRegexp->equals($regexp),
            'Deserialized regexp scope must equal the regexp scope that was serialized'
        );
    }

    /**
     * @test
     * @group        metadata
     *
     * @dataProvider invalidDeserializationDataProvider
     * @expectedException InvalidArgumentException
     *
     * @param mixed $invalidData
     */
    public function deserialization_requires_valid_data($invalidData)
    {
        ShibbolethMetadataScope::deserialize($invalidData);
    }

    /**
     * @return array
     */
    public function invalidDeserializationDataProvider()
    {
        return array(
            'data is not an array' => array('foobar'),
            'missing both keys'    => array(array('a')),
            'missing scope key'    => array('regexp' => false),
            'missing regexp key'   => array('a' => 'foobar', 'scope' => 'foobar'),
        );
    }

    /**
     * @test
     * @group metadata
     */
    public function a_scope_can_be_cast_to_a_known_format_string()
    {
        $scope = ShibbolethMetadataScope::literal('foo');

        $this->assertEquals('ShibbolethMetadataScope(scope=foo, regexp=false)', (string) $scope);
    }
}
