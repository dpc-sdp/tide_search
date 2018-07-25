Feature: Ensure Search API on Bay Elasticsearch work.

  @api @nosuggest
  Scenario: Check for Elasticsearch and search test content.

    Given I am logged in as a user with the "administrator" role
    When I go to "admin/config/search/elasticsearch-connector"
    And I save screenshot
    Then I should see the text "ElasticSearch on Bay"

    Given I am logged in as a user with the "administrator" role
    When I go to "admin/config/search/search-api"
    And I save screenshot

    # Ensure Bay Elasticsearch exist
    When I visit "http://elasticsearch:9200/_cat/indices?v"
    Then the response status code should be 200
    And the response should contain "elasticsearch_index_drupal_node"

    # Prepare test content.
    Given topic terms:
      | name       | parent |
      | Test Topic | 0      |
    Given tags terms:
      | name     | parent |
      | Test Tag | 0      |
    Given test content:
      | title                | body:value | moderation_state | field_topic | field_tags |
      | TESTTITLEPUBLISHED   | TESTBODY   | published        | Test Topic  | Test Tag   |

    # Published Test content should be indexed upon save.
    Given I am logged in as a user with the "approver" role
    When I edit test "TESTTITLEPUBLISHED"
    Then the response status code should be 200
    And I select "Published" from "Change to"
    And I press "Save"
    And I wait for 5 seconds

    # Published Test content should be in search results.
    When I visit "http://elasticsearch:9200/_cat/indices?v"
    Then the response status code should be 200
    And I save screenshot


    When I send a GET request to "http://elasticsearch:9200/elasticsearch_index_drupal_node/_search?q=title:testtitlepublished"
    Then the rest response status code should be 200
    And the response should be in JSON
    And the JSON node "hits" should exist
    And the JSON node "hits.hits" should exist
    And the JSON node "hits.hits[0]._index" should be equal to "elasticsearch_index_drupal_node"
    And the JSON node "hits.hits[0]._type" should be equal to "node"
    And the JSON node "hits.hits[0]._source" should exist
    And the JSON node "hits.hits[0]._source.title[0]" should be equal to "TESTTITLEPUBLISHED"
    And the JSON node "hits.hits[0]._source.body[0]" should be equal to "TESTBODY"
    And the JSON node "hits.hits[0]._source.field_topic_name[0]" should be equal to "Test Topic"
    And the JSON node "hits.hits[0]._source.field_tags_name[0]" should be equal to "Test Tag"

    # Unpublished Test content.
    When I edit test "TESTTITLEPUBLISHED"
    Then the response status code should be 200
    And I select "Archived" from "Change to"
    And I press "Save"
    And I wait for 5 seconds

    # Unpublished Test content should not be in search results.
    When I send a GET request to "http://elasticsearch:9200/elasticsearch_index_drupal_node/_search?q=title:testtitlepublished"
    Then the rest response status code should be 200
    And the response should be in JSON
    And the JSON node "hits" should exist
    And the JSON node "hits.total" should be equal to "0"
