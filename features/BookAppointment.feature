Feature:
  In order to attend a medical appointment
  As a user
  I want to book an appointment with a health specialist

  Scenario: I can book a medical appointment
    Given "Kevin Costner" is a "dentist"
    And "Kevin Costner" is available on "tomorrow" at "09:00"
    When I book an appointment with "Kevin Costner" on "tomorrow" at "09:00"
    Then "Kevin Costner" should have an appointment on "tomorrow" at "09:00"
    And "Kevin Costner" should no longer be available for new appointments on "tomorrow" at "09:00"