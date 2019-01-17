<?php

function NASK_getConfigArray() {
    $configArray = array(
        "Username" => array("type" => "text", "size" => "20", "description" => _("Enter your username here"),),
        "Password" => array("type" => "password", "size" => "20", "description" => _("Enter your password here"),),
        "TestMode" => array("type" => "yesno",)
    );
    return $configArray;
}

function NASK_RegisterNameservers($params) {
    $return = array();

    return $return;
}

function NASK_GetNameserversStatus($params) {
    return array("completed" => true);
}

function NASK_GetNameservers($params) {
    $return = array();

    return $return;
}

function NASK_SaveNameservers($params) {
    $return = array();

    return $return;
}

function NASK_SaveRegistrarLock($params) {
    $return = array();

    if ($params["lockenabled"] == 'locked') {
    } else {
    }

    return $return;
}

function NASK_IDProtectToggle($params) {
    $return = array();

    if ($params["protectenable"] == 'enable') {
    } else {
    }

    return $return;
}

function NASK_SetDNSSEC($params) {
    $return = array();

    foreach ($params as $name => $value) {
        $valtype = gettype($value);
        if ($valtype != "array") {
            pa_log("param: " . $name . " = " . $value . " of type: " . gettype($value), "INFO");
        }
    }

    if (array_key_exists("dnsSecInfo", $params)) {
        pa_log("enabling DNSSEC for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
        $dnsSecInfo = $params["dnsSecInfo"];
        $keys = $dnsSecInfo;
        foreach ($keys as $key) {
            pa_log("KSK key " . $key["digestAlg"] . " digest: " . $key["digest"], "INFO");
        }
    } else {
        pa_log("disabling DNSSEC for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
    }

    return $return;
}

function NASK_SetLocalPresence($params) {
    $return = array();

    foreach ($params as $name => $value) {
        $valtype = gettype($value);
        if ($valtype != "array") {
            pa_log("param: " . $name . " = " . $value . " of type: " . gettype($value), "INFO");
        }
    }

    if ($params["localPresenceInfo"] == 'enable') {
        pa_log("enabling LocalPresence for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
    } else {
        pa_log("disabling LocalPresence for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
    }

    return $return;
}

function NASK_RegisterDomain($params) {
    if (array_key_exists('LOCAL_PRESENCE', $params['additionalfields'])) {
        if ($params['additionalfields']['LOCAL_PRESENCE'] == 1) {
            pa_log("enabling LocalPresence for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
        }
    }
    $return = array(
        "additionalfields" => array(
            "Passwd" => "DUMMYREGSECRET", //"Passwd" name is recommended (it will be hidden from log files)
            "dummyexpirydate" => date('Y-m-d', strtotime('+' . $params["regperiod"] . ' year'))
        )
    );
    if ($params['TestMode'] == 'on') {
        $return["lockenabled"] = 'locked';
    }
    return $return;
}

function NASK_Sync($params) {
  if (array_key_exists('dummyexpirydate', $params['additionalfields'])) {
      if(strpos($params["sld"], 'conflict') !== false) {
          return array(
              "expirydate" => date('Y-m-d', strtotime($params['additionalfields']['dummyexpirydate'])),
              "completed" => true,
              "conflictcode" => "103",
              "conflictmessage" => "Domain has been transferred out!",
          );
      } else if(strpos($params["sld"], 'autorenew') !== false) {
          $regAutoRenew = $params['additionalfields']['Subscription_IsAutoRenew'] == "1" ? "off" : "on"; //inverting result
          $message = "Domain Auto Renewal is ".$regAutoRenew." on registrar side";
          return array(
              "expirydate" => date('Y-m-d', strtotime($params['additionalfields']['dummyexpirydate'])),
              "completed" => true,
              "conflictcode" => "102",
              "conflictmessage" => $message,
          );
      } else {
          return array(
              "expirydate" => date('Y-m-d', strtotime($params['additionalfields']['dummyexpirydate'])),
              "completed" => true,
          );
      }
  } else { //upgrade from 6.5 case
        $dummyexpirydate = date('Y-m-d', strtotime('+' . $params['additionalfields']['regperiod'] . ' year'));
        return array(
            "expirydate" => $dummyexpirydate,
            "completed" => true
        );
  }
}

function NASK_TransferDomain($params) {
    if (array_key_exists('LOCAL_PRESENCE', $params['additionalfields'])) {
        if ($params['additionalfields']['LOCAL_PRESENCE'] == 1) {
            pa_log("enabling LocalPresence for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
        }
    }
    $return = array(
        "additionalfields" => array(
            "transfersecret" => "DUMMYTRANSFERSECRET", //"transfersecret" name is recommended (it will be hidden from log files)
            "dummyexpirydate" => date('Y-m-d', strtotime('+' . $params["regperiod"] . ' year'))
        )
    );
    return $return;
}

function NASK_TransferSync($params) {
    return array(
        "expirydate" => date('Y-m-d', strtotime($params['additionalfields']['dummyexpirydate'])),
        "completed" => true
    );
}

function NASK_RenewDomain($params) {
    if (array_key_exists('dummyexpirydate', $params['additionalfields'])) {
        $return = array(
            "additionalfields" => array(
                "dummyexpirydate" => date('Y-m-d', strtotime('+' . $params["regperiod"] . ' year', strtotime($params['additionalfields']['dummyexpirydate'])))
            )
        );
    } else { //upgrade from 6.5 case
        $dummyexpirydate = date('Y-m-d', strtotime('+' . strval(intval($params['additionalfields']['regperiod']) + intval($params["regperiod"])) . ' year'));
        $return = array(
             "additionalfields" => array(
                 "dummyexpirydate" => $dummyexpirydate,
             )
        );
    }
    if (array_key_exists('LOCAL_PRESENCE', $params['additionalfields'])) {
        if ($params['additionalfields']['LOCAL_PRESENCE'] == 1) {
            pa_log("enabling LocalPresence for domain: " . $params["sld"] . "." . $params["tld"], "INFO");
        }
    }
    return $return;
}

function NASK_RenewSync($params) {
    return array(
        "expirydate" => date('Y-m-d', strtotime($params['additionalfields']['dummyexpirydate'])),
        "completed" => true
    );
}

function NASK_RegisterContacts($params) {
    $return = array();

    return $return;
}

function NASK_GetContactsStatus($params) {
    return array(
        "completed" => true,
        "additionalfields" => array(
            "ownerContactId" => "1",
            "adminContactId" => "2",
            "techContactId" => "3",
            "billingContactId" => "4"
        )
    );
}

function NASK_GetContactDetails($params) {
    $return = array();

    return $return;
}

function NASK_SaveContactDetails($params) {
    $return = array();

    return $return;
}

function NASK_RequestDelete($params) {
    $result = array();
    return $result;
}

function NASK_RequestDeleteSync($params) {
    return array("completed" => true);
}

function NASK_checkAvailability($params) {
    pa_log("Enter checkAvailability" . print_r(json_encode($params), TRUE), "INFO");
    $result = array();
    foreach ($params["domainNames"] as $p) {
        if ($params['TestMode'] == 'on') {
            if ($p == "registered.tv" || $p == "test.cz") {
                $result[$p] = array('status' => 2, 'msg' => _('This domain is not available'));
                $result["newdomain.tv"] = array('status' => 1, 'msg' => _('This domain is available'));
            } else {
                $result[$p] = array('status' => 1, 'msg' => _('This domain is available'));
            }
            if ($params["Username"] == 'suggest_domains_test') {
                $result["sugg1" . $p] = array('status' => 1, 'msg' => _('This domain is available'));
            }
        } else {
            $result[$p] = array('status' => 1, 'msg' => _('This domain is available'));
        }
    }
    pa_log("Exit checkAvailability with success. Result: ".print_r(json_encode($result), TRUE), "INFO");
    return $result;
}

function NASK_checkTransfer($params) {
    return array('status' => 1, 'renewRequired' => 0, 'msg' => _('This domain is available for transfer'));
}

function NASK_ValidateAdditionalFields($params) {
    if ($params['TestMode'] == 'on') {
        $validate_statuses = array(
            'EDIS_OK' => 1,
            'EDIS_FAILED' =>2
        );

        if($params['tld'] == 'de'){
            $status = $validate_statuses['EDIS_OK'];
            $field1 = array("type" => "text", "title" => _("Tax ID"), "description" => "", "defaultValue" => "", "required" => 1, "status" => $status);
            $field2 = array("type" => "tickbox", "title" => _("Address Confirmation"), "description" => _("Please confirm you have a valid German address"), "defaultValue" => "0", "required" => 0, "status" => $status);
            if($params['operationType'] == 1){ //process of registration
                if(isset($params['additionalfields']['de_TaxID']) || isset($params['additionalfields']['de_AddressConfirmation'])){
                    if(!is_null($params['additionalfields']['de_TaxID']) && strlen($params['additionalfields']['de_TaxID']) > 1)
                        $field1["status"] = $validate_statuses['EDIS_OK'];
                    else {
                        $field1["status"] = $validate_statuses['EDIS_FAILED'];
                        $field1["message"] = _("You should fill Tax ID correctly");
                    }
                    $field2["status"] = $validate_statuses['EDIS_OK'];
                }
            }
            $extFields = array(
                "de_TaxID" => $field1,
                "de_AddressConfirmation" => $field2,
            );
        }

        if($params['tld'] == 'se'){
            $status = $validate_statuses['EDIS_OK'];
            $field1 = array("type" => "text", "title" => _("Company number"), "description" => "", "defaultValue" => "", "required" => 1, "status" => $status);
            if($params['operationType'] == 1){ //process of registration
                if(isset($params['additionalfields']['sereg_value'])){
                    if(!is_null($params['additionalfields']['sereg_value']) && strlen($params['additionalfields']['sereg_value']) > 1)
                        $field1["status"] = $validate_statuses['EDIS_OK'];
                    else {
                        $field1["status"] = $validate_statuses['EDIS_FAILED'];
                        $field1["message"] = _("You should to fill Company number correctly");
                    }
                }
            }
            $extFields = array(
                "sereg_value" => $field1,
            );
        }

		if($params['tld'] == 'es'){ //fake handler for testing
			 $status = $validate_statuses['EDIS_OK'];
			 $extFields["entityType"] = array("type" => "dropdown",
											  "title" => "Entity Type",
											  "defaultValue" => "default",
											  "options" => "1|"._("Individual").",39|"._("Economic Interest Group").",47|"._("Association").",59|"._("Sports Association").",68|"._("Professional Association").",124|"._("Savings Bank").",150|"._("Community Property").",152|"._("Community of Owners").",164|"._("Order or Religious Institution").",181|"._("Consulate").",197|"._("Public Law Association").",203|"._("Embassy").",229|"._("Local Authority").",269|"._("Sports Federation").",286|"._("Foundation").",365|"._("Mutual Insurance Company").",434|"._("Regional Government Body").",436|"._("Central Government Body").",439|"._("Political Party").",476|"._("Trade Union").",510|"._("Farm Partnership").",524|"._("Public Limited Company").",525|"._("Sports Association").",554|"._("Civil Society").",560|"._("General Partnership").",562|"._("General and Limited Partnership").",566|"._("Cooperative").",608|"._("Worker-owned Company").",612|"._("Limited Company").",713|"._("Spanish Office").",717|"._("Temporary Alliance of Enterprises").",744|"._("Worker-owned Limited Company").",745|"._("Regional Public Entity").",746|"._("National Public Entity").",747|"._("Local Public Entity").",877|"._("Others").",878|"._("Designation of Origin Supervisory (only for contacts outsight of Spain) Council").",879|"._("Entity Managing Natural Areas").",default|"._("-- Select your entity type --"),
											  "description"  => _("Choose your Entity Type. If legal registrant is a person outside of Spain choose 'Individual'. If legal registrant is a business outside of Spain choose 'Others'."),
											  "required" => 1,
											  "status" => $status);

			$entType = $params['additionalfields']['entityType'];

			if(isset($entType) && $entType != 'default'){

			 $options = "OTHER|Other form of ID (Those outside of Spain)";
			 $extFields["identificationType"] = array("type" => "dropdown",
													  "title" => "Identification Type",
													  "defaultValue" => "OTHER",
													  "options" => $options,
													  "description" => _("If legal registrant is a person from Spain choose NIF or NIE. If legal registrant is a business in Spain choose CIF. If legal registrant is a person or a business outside of Spain choose 'Other form of ID'."),
													  "required" => 1,
													  "status" => $status);

			 $identType = $params['additionalfields']['identificationType'];
			 pa_log("identType: " . $identType);

				if ($entType == '1'){
						$options =  "CITIZEN|"._("NIF (Spanish citizen)").",RESIDENT|"._("NIE (Legal residents in Spain)").",OTHER|"._("Other form of ID (Those outside of Spain)");
						$extFields["identificationType"]["options"] = $options;

						if (isset($identType) && ($identType == 'CITIZEN'))  {
								  $extFields["NIF"] = array("type"        => "text",
															"title"       => "NIF",
															"description" => "NIF (Spanish citizen)",
															"required"    => 1,
															"status"      => $status);
						} else if(isset($identType) && ($identType == 'RESIDENT')){
								  $extFields["NIE"] = array("type"        => "text",
															"title"       => "NIE",
															"description" => "NIE (Legal residents in Spain)",
															"required"    => 1,
															"status"      => $status);
						} else {
								   $extFields["OTHER"] = array("type"    => "text",
															   "title"       => "Other form of ID",
															   "description" => "Other form of ID (Those outside of Spain)",
															   "required"    => 1,
															   "status"      => $status);
						}
			  } else if ($entType == '47') {
						$options = "COMPANY|"._("CIF (Spanish company)").",OTHER|"._("Other form of ID (Those outside of Spain)");
						$extFields["identificationType"]["options"] = $options;

						if(isset($identType) && ($identType == 'COMPANY')){
								  $extFields["CIF"] = array("type"    => "text",
															"title"       => "CIF",
															"description" => "CIF (Spanish company)",
															"required"    => 1,
															"status"      => $status);
						}
						else {
								   $extFields["OTHER"] = array("type"    => "text",
															   "title"       => "Other form of ID",
															   "description" => "Other form of ID (Those outside of Spain)",
															   "required"    => 1,
															   "status"      => $status);
						}
              } else {
						$options = "ID|"._("ID number (Foreign company)").",OTHER|"._("Other form of ID (Those outside of Spain)");
						$extFields["identificationType"]["options"] = $options;

						if(isset($identType) && ($identType == 'ID')){
								   $extFields["ID"] = array("type"    => "text",
															"title"       => "ID number",
															"description" => "ID number (Foreign company)",
															"required"    => 1,
															"status"      => $status);
						} else {
								   $extFields["OTHER"] = array("type"    => "text",
															   "title"       => "Other form of ID",
															   "description" => "Other form of ID (Those outside of Spain)",
															   "required"    => 1,
															   "status"      => $status);
						}
			  }
			}
		}

        if($params['tld'] == 'cz') { //fake handler for testing
            $status = $validate_statuses['EDIS_OK'];
            $field1 = array("type" => "text", "title" => _("Tax ID"), "description" => "", "defaultValue" => "", "required" => 1, "status" => $status);
            $field2 = array("type" => "tickbox", "title" => _("Address Confirmation"), "description" => _("Please confirm you have a valid German address"), "defaultValue" => "0", "required" => 0, "status" => $status);
            $field3 = array("type" => "dropdown", "title" => _("Some random dropdown"), "description" => _("Select something"), "defaultValue" => "SUPER", "options" => "MEGA|"._("MEGA").",UBER|"._("UBER").",SUPER|"._("SUPER"), "required" => 0, "status" => $status);
            $field5 = array("type" => "text", "title" => "one", "description" => "", "defaultValue" => "", "required" => 1, "status" => $status);
            $field6 = array("type" => "text", "title" => "two", "description" => "", "defaultValue" => "", "required" => 1, "status" => $status);
            $field7 = array("type" => "dropdown", "title" => _("Random dependant dropdown"), "description" => _("Select something"), "defaultValue" => "UBER", "options" => "", "required" => 0, "status" => $status);
            if($params['operationType'] == 1){ //process of registration
                if(isset($params['additionalfields']['cz_TaxID']) || isset($params['additionalfields']['cz_AddressConfirmation'])){
                    if(!is_null($params['additionalfields']['cz_TaxID']) && strlen($params['additionalfields']['cz_TaxID']) > 1)
                        $field1["status"] = $validate_statuses['EDIS_OK'];
                    else {
                        $field1["status"] = $validate_statuses['EDIS_FAILED'];
                        $field1["message"] = _("You should fill Tax ID correctly");
                    }
                    $field2["status"] = $validate_statuses['EDIS_OK'];
                }
            }
            if (isset($params['additionalfields']['cz_AddressConfirmation']) && $params['additionalfields']['cz_AddressConfirmation'] == '1') {
                $extFields = array(
                    "cz_TaxID" => $field1,
                    "cz_AddressConfirmation" => $field2,
                    "cz_SomeRandomDropdown" => $field3
                );

                if ((isset($params['additionalfields']['cz_SomeRandomDropdown']) &&  $params['additionalfields']['cz_SomeRandomDropdown'] == 'UBER') ||
                    (!isset($params['additionalfields']['cz_SomeRandomDropdown']) && $extFields['cz_SomeRandomDropdown']['defaultValue'] == 'UBER')) {
                    $field7["options"] = "MEGA|"._("MEGA").",UBER|"._("UBER").",SUPER|"._("SUPER").",SUPER1|"._("SUPER1");
                    $extFields = array(
                        "cz_TaxID" => $field1,
                        "cz_AddressConfirmation" => $field2,
                        "cz_SomeRandomDropdown" => $field3,
                        "cz_one" => $field5,
                        "cz_RandomDependantDropdown" => $field7
                    );
                } else {
                    $field7["options"] = "MEGA|"._("MEGA").",UBER|"._("UBER").",SUPER|"._("SUPER").",SUPER2|"._("SUPER2");
                    $extFields = array(
                        "cz_TaxID" => $field1,
                        "cz_AddressConfirmation" => $field2,
                        "cz_SomeRandomDropdown" => $field3,
                        "cz_two" => $field6,
                        "cz_RandomDependantDropdown" => $field7
                    );
                }

            } else {
                $extFields = array(
                    "cz_TaxID" => $field1,
                    "cz_AddressConfirmation" => $field2,
                );
            }
        }
    }

    return $extFields;
}

function NASK_GetEPPCode($params) {
    if ($params['TestMode'] == 'on') {
        $domainName = $params["sld"] . "." . $params["tld"];
        $eppCode = "dummy-code-for-" . $domainName;
        if( strpos($params["sld"], 'test-asynchronous') !== false ) {
            return array('eppcode' => "", 'msg' => _("Domain data has been inquired successfully."), 'pending'=>1);
        }
        return array('eppcode' => $eppCode, 'completed' => true, 'msg' => _("Domain data has been inquired successfully."), 'failed' => false);
    } else {
        return array('error' => _("This operation cannot be performed for this domain."));
    }
}

function NASK_SetAutoRenew($params) {
    $return = array();

    if (array_key_exists("autorenew", $params)) {
        pa_log("setting autorenew flag for domain: " . $params["sld"] . "." . $params["tld"] . ", new value: " . $params["autorenew"], "INFO");
    } else {
        pa_log("AutoRenew flag not found in incoming request: " . $params["sld"] . "." . $params["tld"], "INFO");
    }

    return $return;
}

function NASK_GetAutoRenewStatus($params) {
    return array("completed" => true);
}

