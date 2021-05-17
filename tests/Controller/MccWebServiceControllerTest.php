<?php

namespace Drupal\mccserver\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the mccserver module.
 */
class MccWebServiceControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "mccserver MccWebServiceController's controller functionality",
      'description' => 'Test Unit for module mccserver and controller MccWebServiceController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests mccserver functionality.
   */
  public function testMccWebServiceController() {
    // Check that the basic functions of module mccserver.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
