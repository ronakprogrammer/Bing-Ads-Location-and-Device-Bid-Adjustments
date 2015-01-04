<?php
ini_set('max_execution_time',0);
ini_set('display_errors', 1);
ini_set("soap.wsdl_cache_enabled", "0");
ini_set("soap.wsdl_cache_ttl", "0");
error_reporting(E_ALL | E_STRICT);
require_once ('constants.php');
require_once ('common_functions.php');

$refreshToken = BING_REFRESH_TOKEN;
$data = refreshAccessToken($refreshToken);


$accessToken = trim($data['access_token']);

include 'bingads/CampaignManagementClasses.php';
include 'bingads/ClientProxy.php'; 

// Specify the BingAds\CampaignManagement objects that will be used.

use BingAds\CampaignManagement\AddTargetsToLibraryRequest;
use BingAds\CampaignManagement\AddTargetsToLibrary2Request;
use BingAds\CampaignManagement\SetTargetToAdGroupRequest;
use BingAds\CampaignManagement\SetTargetToCampaignRequest;
use BingAds\CampaignManagement\Target;
use BingAds\CampaignManagement\Target2;
use BingAds\CampaignManagement\DeviceOSTarget;
use BingAds\CampaignManagement\DeviceOSTargetBid;
use BingAds\CampaignManagement\LocationTarget;
use BingAds\CampaignManagement\LocationTargetBid;
use BingAds\CampaignManagement\LocationTarget2;
use BingAds\CampaignManagement\LocationTargetBid2;
use BingAds\CampaignManagement\CountryTarget;
use BingAds\CampaignManagement\CountryTargetBid;
use BingAds\CampaignManagement\IntentOption;
use BingAds\CampaignManagement\GetTargetsByCampaignIds2Request;
use BingAds\CampaignManagement\UpdateTargetsInLibrary2Request;

// Specify the BingAds\Proxy objects that will be used.
use BingAds\Proxy\ClientProxy;




function AddDeviceTarget($accessToken,$campaignId,$device,$bid_adjustment,$os_names = array()){
    $UserName = NULL;
    $Password = NULL;
    $DeveloperToken = BING_DEVELOPER_TOKEN; 
    $CustomerId = BING_CUSTOMER_ID;
    $AccountId = BING_ACCOUNT_ID;
    $flag = false;
    
     
    // Campaign Management WSDL
    $wsdl = "https://api.bingads.microsoft.com/Api/Advertiser/CampaignManagement/V9/CampaignManagementService.svc?singleWsdl";
    try
    {
        $proxy = ClientProxy::ConstructWithAccountAndCustomerId($wsdl, $UserName, $Password, $DeveloperToken, $AccountId, $CustomerId, $accessToken);
        $campaignTarget = new Target2();

        $deviceOSTarget = new DeviceOSTarget();
        $deviceOSTargetBid = new DeviceOSTargetBid();
        $deviceOSTargetBid->BidAdjustment = $bid_adjustment;
        $deviceOSTargetBid->DeviceName = $device;
        if($device == 'Tablets'){
            $deviceOSTargetBid->OSNames = NULL;
        } elseif(!empty($os_names)){
            $deviceOSTargetBid->OSNames = $os_names;
        }
        $deviceOSTarget->Bids = array($deviceOSTargetBid);
        $campaignTarget->DeviceOS = $deviceOSTarget;

        // Add a target to the library and associate it with the campaign.
        $campaignTargetId = AddTargetsToLibrary2($proxy, array($campaignTarget))->long[0];

        printf("Added Target Id: %d\n\n", $campaignTargetId);
        SetTargetToCampaign($proxy, $campaignId, $campaignTargetId);
        printf("Associated CampaignId %s with TargetId %s.\n\n", $campaignId, $campaignTargetId);
        $flag = TRUE;
    } catch (SoapFault $e){
        echo "Error :".$e->getMessage()."\n";
    }
    
    return $flag;
}

function AddLocationTarget($accessToken,$campaignId,$country_code,$bid_adjustment){
    $UserName = NULL;
    $Password = NULL;
    $DeveloperToken = BING_DEVELOPER_TOKEN; 
    $CustomerId = BING_CUSTOMER_ID;
    $AccountId = BING_ACCOUNT_ID;
    $flag = false;
    
     
    // Campaign Management WSDL
    $wsdl = "https://api.bingads.microsoft.com/Api/Advertiser/CampaignManagement/V9/CampaignManagementService.svc?singleWsdl";
    try
    {
        $proxy = ClientProxy::ConstructWithAccountAndCustomerId($wsdl, $UserName, $Password, $DeveloperToken, $AccountId, $CustomerId, $accessToken);
        
        $campaignTarget = new Target2();
        
        $locationTarget = new LocationTarget2();
        $locationTarget->IntentOption = IntentOption::PeopleSearchingForOrViewingPages;
        
        $countryTarget = new CountryTarget();
        $countryTargetBid = new CountryTargetBid();
        $countryTargetBid->BidAdjustment = $bid_adjustment;
        $countryTargetBid->CountryAndRegion = $country_code;
        $countryTargetBid->IsExcluded = false;
        $countryTarget->Bids = array($countryTargetBid);
        
        $locationTarget->CountryTarget = $countryTarget;
        
        $campaignTarget->Location = $locationTarget;

        // Add a target to the library and associate it with the campaign.
        $campaignTargetId = AddTargetsToLibrary2($proxy, array($campaignTarget))->long[0];

        printf("Added Target Id: %d\n\n", $campaignTargetId);
        SetTargetToCampaign($proxy, $campaignId, $campaignTargetId);
        printf("Associated CampaignId %s with TargetId %s.\n\n", $campaignId, $campaignTargetId);
        $flag = TRUE;
    } catch (SoapFault $e){
        echo "Error :".$e->getMessage()."\n";
    }
    
    return $flag;
}

function getTargetByCampaign($accessToken,$campaignId){
    $UserName = NULL;
    $Password = NULL;
    $DeveloperToken = BING_DEVELOPER_TOKEN; 
    $CustomerId = BING_CUSTOMER_ID;
    $AccountId = BING_ACCOUNT_ID;
    $campaignTargets = array();
    
    // Campaign Management WSDL
    $wsdl = "https://api.bingads.microsoft.com/Api/Advertiser/CampaignManagement/V9/CampaignManagementService.svc?singleWsdl";
    
    try{
        $proxy = ClientProxy::ConstructWithAccountAndCustomerId($wsdl, $UserName, $Password, $DeveloperToken, $AccountId, $CustomerId, $accessToken);
        $targets = GetTargetsByCampaignIds2($proxy, $campaignId);
        
        
        if(!empty($targets->Target2)){
            $campaignTargets = $targets->Target2[0];
        }
    } catch (Exception $ex) {
        echo "Error : ".$ex->getMessage()."\n";
    }
    
    return $campaignTargets;
}


// Adds the specified Target object to the customer library. 
// The operation requires exactly one Target in a list.
function AddTargetsToLibrary($proxy, $targets)
{
    $request = new AddTargetsToLibraryRequest();
    $request->Targets = $targets;
    
    return $proxy->GetService()->AddTargetsToLibrary($request)->TargetIds;
}

function AddTargetsToLibrary2($proxy, $targets)
{
    $request = new AddTargetsToLibrary2Request();
    $request->Targets = $targets;
    
    return $proxy->GetService()->AddTargetsToLibrary2($request)->TargetIds;
}


// Associates the specified campaign and target.
function SetTargetToCampaign($proxy, $campaignId, $targetId)
{
    $request = new SetTargetToCampaignRequest();
    $request->CampaignId = $campaignId;
    $request->ReplaceAssociation = TRUE;
    $request->TargetId = $targetId;
    
    $proxy->GetService()->SetTargetToCampaign($request);
}

function GetTargetsByCampaignIds2($proxy,$campaignId){
    $request = new GetTargetsByCampaignIds2Request();
    $request->CampaignIds = array($campaignId);
    return $proxy->GetService()->GetTargetsByCampaignIds2($request)->Targets;
}

function UpdateTargetsInLibrary2($proxy, $targets)
{
    $request = new UpdateTargetsInLibrary2Request();
    $request->Targets = $targets;
    
    $proxy->GetService()->UpdateTargetsInLibrary2($request);
}


function UpdateDeviceTarget($targets,$accessToken,$device,$bid_adjustment){
    $UserName = NULL;
    $Password = NULL;
    $DeveloperToken = BING_DEVELOPER_TOKEN; 
    $CustomerId = BING_CUSTOMER_ID;
    $AccountId = BING_ACCOUNT_ID;
    $flag = false;
    
    // Campaign Management WSDL
    $wsdl = "https://api.bingads.microsoft.com/Api/Advertiser/CampaignManagement/V9/CampaignManagementService.svc?singleWsdl";
    try
    {
        $proxy = ClientProxy::ConstructWithAccountAndCustomerId($wsdl, $UserName, $Password, $DeveloperToken, $AccountId, $CustomerId, $accessToken);
        $campaignTarget = new Target2();
        $campaignTarget->Id = $targets->Id;

        $deviceOSTarget = new DeviceOSTarget();
        $deviceOSTargetBid = new DeviceOSTargetBid();
        $deviceOSTargetBid->BidAdjustment = $bid_adjustment;
        $deviceOSTargetBid->DeviceName = $device;
        
        
        
        if(!empty($targets->DeviceOS)){
            $deviceTargetBids = $targets->DeviceOS->Bids->DeviceOSTargetBid;
        }
        
        $smartPhoneTarget = new DeviceOSTargetBid();
        $tabletTarget = new DeviceOSTargetBid();
        $computerTarget = new DeviceOSTargetBid();
        
        if($device == 'Tablets'){
            
            if(!empty($deviceTargetBids)){
                foreach ($deviceTargetBids as $target){
                    if($target->DeviceName == 'Smartphones'){
                        $smartPhoneTarget->DeviceName = $target->DeviceName;
                        $smartPhoneTarget->BidAdjustment = $target->BidAdjustment;
                        $smartPhoneTarget->OSNames = $target->OSNames;
                    } else if($target->DeviceName == 'Computers'){
                        $computerTarget->DeviceName = $target->DeviceName;
                        $computerTarget->BidAdjustment = $target->BidAdjustment;
                        $computerTarget->OSNames = $target->OSNames;
                    }
                }
            }
        } else if($device == 'Smartphones'){
            
            if(!empty($deviceTargetBids)){
                foreach ($deviceTargetBids as $target){
                    if($target->DeviceName == 'Tablets'){
                        $tabletTarget->DeviceName = $target->DeviceName;
                        $tabletTarget->BidAdjustment = $target->BidAdjustment;
                        $tabletTarget->OSNames = $target->OSNames;
                    } else if($target->DeviceName == 'Computers'){
                        $computerTarget->DeviceName = $target->DeviceName;
                        $computerTarget->BidAdjustment = $target->BidAdjustment;
                        $computerTarget->OSNames = $target->OSNames;
                    } else if($target->DeviceName == 'Smartphones'){
                        if(!empty($target->OSNames) && !empty($target->OSNames->string)){
                            $deviceOSTargetBid->OSNames = $target->OSNames->string;
                        }
                    }
                }
            }
        }
        
        $finalTargetBids = array($deviceOSTargetBid);
        
        if($device == 'Computers'){
            if(!empty($smartPhoneTarget->DeviceName) && empty($tabletTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$smartPhoneTarget);
            }
            
            if(!empty($tabletTarget->DeviceName) && empty($smartPhoneTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$tabletTarget);
            }
            
            if(!empty($tabletTarget->DeviceName) && !empty($smartPhoneTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$tabletTarget,$smartPhoneTarget);
            }    
        } else if($device == 'Tablets'){
            if(!empty($smartPhoneTarget->DeviceName) && empty($computerTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$smartPhoneTarget);
            }
            
            if(!empty($computerTarget->DeviceName) && empty($smartPhoneTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$computerTarget);
            }
            
            if(!empty($computerTarget->DeviceName) && !empty($smartPhoneTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$computerTarget,$smartPhoneTarget);
            }
        } else if($device == 'Smartphones'){
            if(!empty($tabletTarget->DeviceName) && empty($computerTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$tabletTarget);
            }
            
            if(!empty($computerTarget->DeviceName) && empty($tabletTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$computerTarget);
            }
            
            if(!empty($computerTarget->DeviceName) && !empty($tabletTarget->DeviceName)){
                $finalTargetBids = array($deviceOSTargetBid,$computerTarget,$tabletTarget);
            }
        }
        
        
        //pr($finalTargetBids); die;
        
        $deviceOSTarget->Bids = $finalTargetBids;
        $campaignTarget->DeviceOS = $deviceOSTarget;
        if(!empty($targets->Location)){
            $campaignTarget->Location = $targets->Location;
        }
        
        if(!empty($targets->Age)){
            $campaignTarget->Age = $targets->Age;
        }
        
        if(!empty($targets->DayTime)){
            $campaignTarget->DayTime = $targets->DayTime;
        }
        
        if(!empty($targets->Gender)){
            $campaignTarget->Gender = $targets->Gender;
        }
        
        if(!empty($targets->Network)){
            $campaignTarget->Network = $targets->Network;
        }
        
        // Updates target to the library and associate it with the campaign.
        UpdateTargetsInLibrary2($proxy, array($campaignTarget));
        $flag = TRUE;
    } catch (SoapFault $e){
        echo "Error :".$e->getMessage()."\n";
    }
    
    return $flag;
}

function UpdateLocationTarget($targets,$accessToken,$country_code,$bid_adjustment){
    $UserName = NULL;
    $Password = NULL;
    $DeveloperToken = BING_DEVELOPER_TOKEN; 
    $CustomerId = BING_CUSTOMER_ID;
    $AccountId = BING_ACCOUNT_ID;
    $flag = false;
    
     
    // Campaign Management WSDL
    $wsdl = "https://api.bingads.microsoft.com/Api/Advertiser/CampaignManagement/V9/CampaignManagementService.svc?singleWsdl";
    try
    {
        $proxy = ClientProxy::ConstructWithAccountAndCustomerId($wsdl, $UserName, $Password, $DeveloperToken, $AccountId, $CustomerId, $accessToken);
        
        $campaignTarget = new Target2();
        $campaignTarget->Id = $targets->Id;
        $locationTarget = new LocationTarget2();
        
        if(!empty($targets->Location->IntentOption)){   
            $locationTarget->IntentOption = $targets->Location->IntentOption;
        } else {
            $locationTarget->IntentOption = IntentOption::PeopleSearchingForOrViewingPages;
        }
        $cityTarget = '';
        $metroTarget = '';
        $postalCodeTarget = '';
        $radiusTarget = '';
        $stateTarget = '';
        
        if(!empty($targets->Location->CityTarget)){
            $locationTarget->CityTarget = $targets->Location->CityTarget;
        }
        
        if(!empty($targets->Location->MetroAreaTarget)){
            $locationTarget->MetroAreaTarget = $targets->Location->MetroAreaTarget;
        }
        
        if(!empty($targets->Location->PostalCodeTarget)){
            $locationTarget->PostalCodeTarget = $targets->Location->PostalCodeTarget;
        }
        
        if(!empty($targets->Location->RadiusTarget)){
            $locationTarget->RadiusTarget = $targets->Location->RadiusTarget;
        }
        
        if(!empty($targets->Location->StateTarget)){
            $locationTarget->StateTarget = $targets->Location->StateTarget;
        }
        
        $countryTarget = new CountryTarget();
        $countryTargetBid = new CountryTargetBid();
            
        if(empty($targets->Location->CountryTarget)){
            $countryTargetBid->BidAdjustment = $bid_adjustment;
            $countryTargetBid->CountryAndRegion = $country_code;
            $countryTargetBid->IsExcluded = false;
            $countryTarget->Bids = array($countryTargetBid);
            $locationTarget->CountryTarget = $countryTarget;
        } else {
            $flag = false;
            $bids = array();
            $countryTargetBids = $targets->Location->CountryTarget->Bids->CountryTargetBid;
            foreach ($countryTargetBids as $country_bid){
                
                if($country_bid->CountryAndRegion == $country_code){
                    $country_bid->BidAdjustment = $bid_adjustment;
                    
                    // If Specified Country Target is Excluded then make it 0 to Enable it Again
                    if($country_bid->IsExcluded == 1){
                       $country_bid->IsExcluded = 0; 
                    }
                    
                    $flag = TRUE;
                }
                
                $bids[] = $country_bid;
            }
            
            if(!$flag){
                
                $newCountryTargetBid = new CountryTargetBid();
                $newCountryTargetBid->BidAdjustment = $bid_adjustment;
                $newCountryTargetBid->CountryAndRegion = $country_code;
                $newCountryTargetBid->IsExcluded = false;
                $bids[] = $newCountryTargetBid;
                 
            }
            
            $countryTarget->Bids = $bids;
            $locationTarget->CountryTarget = $countryTarget;
        }
        
        $campaignTarget->Location = $locationTarget;
        
        if(!empty($targets->DeviceOS)){
            $campaignTarget->DeviceOS = $targets->DeviceOS;
        }
        
        if(!empty($targets->Age)){
            $campaignTarget->Age = $targets->Age;
        }
        
        if(!empty($targets->DayTime)){
            $campaignTarget->DayTime = $targets->DayTime;
        }
        
        if(!empty($targets->Gender)){
            $campaignTarget->Gender = $targets->Gender;
        }
        
        if(!empty($targets->Network)){
            $campaignTarget->Network = $targets->Network;
        }
        
        // Updates target to the library and associate it with the campaign.
        UpdateTargetsInLibrary2($proxy, array($campaignTarget));
        $flag = TRUE;
    } catch (SoapFault $e){
        echo "Error :".$e->getMessage()."\n";
    }
    
    return $flag;
}

$targets = getTargetByCampaign($accessToken,$campaignId);
if(empty($targets)){
    //AddDeviceTarget($accessToken,$campaignId, "Smartphones", 3,array('Android','iOS'));
    //AddLocationTarget($accessToken,$campaignId, "US", 4);
} else {
    //UpdateDeviceTarget($targets, $accessToken, "Tablets", 51);
    //UpdateLocationTarget($targets, $accessToken, "IN", 163);
}