<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/simpletest/web_tester.php');
include_once(dirname(__FILE__) . '/config.php');

//
// Test cases for php/submit.php and php/flights.php
// NB: Assumes the test user exists and there are no flights entered yet (run signup.php first!)
//
// TODO: Multiinput

$fid = null; // global for newly-added flight

// Try to add a flight when not logged in
class AddSingleFlightWithoutLoggingInTest extends WebTestCase {
  function test() {
    global $webroot, $settings, $flight, $fid;

    $msg = $this->post($webroot . "php/submit.php", $flight);
    $this->assertText('Not logged in');
  }
}

// Add a single flight
class AddSingleFlightTest extends WebTestCase {
  function test() {
    global $webroot, $settings, $flight, $fid;

    login($this);
    $this->assertText("1;");

    $msg = $this->post($webroot . "php/submit.php", $flight);
    $this->assertText('1;');

    // Check that one flight was added
    $map = $this->post($webroot . "php/map.php");
    $cols = preg_split('/[;\n]/', $map);
    $this->assertTrue($cols[0] == "1", "One flight recorded");

    // Get the ID of the newly-added flight
    $db = db_connect();
    $sql = "SELECT fid FROM flights WHERE note='" . addslashes($flight["note"]) . "'";
    $result = mysql_query($sql, $db);
    $row = mysql_fetch_assoc($result);
    $fid = $row["fid"];
    $this->assertTrue($fid != null && $fid != "");
  }
}

// Fetch and validate newly-added flight
class FetchAddSingleFlightTest extends WebTestCase {
  function test() {
    global $webroot, $settings, $flight, $fid;

    login($this);
    $this->assertText("1;");

    $params = array("fid" => $fid);
    $msg = $this->post($webroot . "php/flights.php", $params);
    $this->assertText($flight["src_date"]);
    $this->assertText($flight["src_apid"]);
    $this->assertText($flight["dst_apid"]);
    $this->assertText($flight["alid"]);
    $this->assertText($flight["duration"]);
    $this->assertText($flight["distance"]);
    $this->assertText($flight["number"]);
    $this->assertText($flight["plane"]);
    $this->assertText($flight["seat"]);
    $this->assertText($flight["type"]);
    $this->assertText($flight["class"]);
    $this->assertText($flight["reason"]);
    $this->assertText($flight["registration"]);
    $this->assertText($flight["note"]);
    $this->assertText($flight["src_time"]);
  }
}

// Edit new flight, altering all fields into flight2
class EditFlightTest extends WebTestCase {
  function test() {
    global $webroot, $settings, $flight2, $fid;

    login($this);
    $this->assertText("1;");

    $params = $flight2;
    $params["fid"] = $fid;
    $msg = $this->post($webroot . "php/submit.php", $params);
    $this->assertText('2;');
  }
}

// Fetch and validate newly-added flight
class FetchEditedFlightTest extends WebTestCase {
  function test() {
    global $webroot, $settings, $flight2, $fid;

    login($this);
    $this->assertText("1;");

    $params = array("fid" => $fid);
    $msg = $this->post($webroot . "php/flights.php", $params);
    $this->assertText($flight2["src_date"]);
    $this->assertText($flight2["src_apid"]);
    $this->assertText($flight2["dst_apid"]);
    $this->assertText($flight2["alid"]);
    $this->assertText($flight2["duration"]);
    $this->assertText($flight2["distance"]);
    $this->assertText($flight2["number"]);
    $this->assertText($flight2["plane"]);
    $this->assertText($flight2["seat"]);
    $this->assertText($flight2["type"]);
    $this->assertText($flight2["class"]);
    $this->assertText($flight2["reason"]);
    $this->assertText($flight2["registration"]);
    $this->assertText($flight2["note"]);
    $this->assertText($flight2["src_time"]);
  }
}

// CSV export and validate edited flight
class CSVExportFlightTest extends WebTestCase {
  function test() {
    global $webroot, $settings, $flight2, $fid;

    login($this);
    $this->assertText("1;");

    $params = array("export" => "export");
    $msg = $this->get($webroot . "php/flights.php", $params);
    $this->assertText($flight2["src_date"] . " " . $flight2["src_time"] . ":00,");
    $this->assertText($flight2["alid"] . ",");
    $this->assertText($flight2["duration"] . ",");
    $this->assertText($flight2["distance"] . ",");
    $this->assertText($flight2["number"] . ",");
    $this->assertText($flight2["plane"] . ",");
    $this->assertText($flight2["seat"] . ",");
    $this->assertText($flight2["type"] . ",");
    $this->assertText($flight2["class"] . ",");
    $this->assertText($flight2["reason"] . ",");
    $this->assertText($flight2["registration"] . ",");
    $this->assertText($flight2["note"]); // may or may not be quote-wrapped
    $this->assertText($flight2["src_apid"] . ",");
    $this->assertText($flight2["dst_apid"] . ",");
    $this->assertText($flight2["alid"] . ",");
  }
}

?>