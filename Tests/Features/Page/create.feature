@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a page

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can create a new page
        When I follow the float action button
        Then I should see "New page"
        And I follow "New page"
        And I wait 2 seconds
        And I should see "Name"
        When I fill in "Name" with "tatooine"
        Then I submit the widget
        And I wait 3 second
        And I should see "Successfully created page"
        And the url should match "/en/tatooine"

    @alice(Template)
    Scenario: I can change the name and the url of a given page
        Given the following Page:
            | currentLocale | name     | slug     | parent | template |
            | en            | tatooine | tatooine | home   | base     |
        And I am on "/en/tatooine"
        And I open the settings menu
        And I should see "UPDATE"
        Then I fill in "Name" with "anoth"
        Then I fill in "page_settings_translations_en_slug" with "anoth"
        And I submit the widget
        And I wait 5 seconds
        Then the url should match "/en/anoth"
        And I should see "Successfully modified page"

    @alice(Template)
    Scenario: I can define a permalink for a page which is not already in use
        Given the following Page:
            | currentLocale | name             | slug             | parent           | template |
            | en            | anakin skywalker | anakin-skywalker | home             | base     |
            | en            | luke skywalker   | luke-skywalker   | anakin-skywalker | base     |
            | en            | contact page     | contact          | home | base     |
        And I am on "/en/anakin-skywalker/luke-skywalker"
        And I open the settings menu
        And I should see "UPDATE"
        Then I fill in "page_settings_translations_en_permalink" with "anakin-skywalker"
        And I submit the widget
        And I wait 5 seconds
        Then the url should match "/en/anakin-skywalker/luke-skywalker"
        And I should see "The url is already in use"
        When I fill in "page_settings_translations_en_permalink" with "contact"
        And I submit the widget
        And I wait 5 seconds
        Then the url should match "/en/anakin-skywalker/luke-skywalker"
        And I should see "The url is already in use"
        Then I fill in "page_settings_translations_en_permalink" with "amazing-luke-skywalker"
        And I submit the widget
        And I wait 5 seconds
        Then the url should match "/en/amazing-luke-skywalker"
        And I should see "Successfully modified page"
