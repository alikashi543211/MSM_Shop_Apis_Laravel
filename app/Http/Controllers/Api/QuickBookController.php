<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuickBook;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\Customer;
use QuickBooksOnline\API\Facades\Item;


class QuickBookController extends Controller
{
    private $quickBook;
    public function __construct()
    {
        $this->quickBook = new QuickBook();
        // Code Guide Link
        // https://developer.intuit.com/app/developer/qbo/docs/develop/sdks-and-samples-collections/php
        // Inventory Management Api Guide Link
        // https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/item

    }

    public function createCustomer()
    {
        $dataService = $this->getInstanceOfDataService();

        // Set the access token
        $accessToken = $this->createInstanceOfOAuth2AccesToken();
        $dataService->updateOAuth2Token($accessToken);
        // Create a new customer object
        $customer = new IPPCustomer();

        // Set the customer's properties
        $customer->GivenName = 'Kashif Api Code';
        $customer->FamilyName = 'Kashif Api Family';
        $customer->FullyQualifiedName = 'John Doe';
        $customer->PrimaryEmailAddr = 'kashifapicode.doe@example.com';

        // Create the customer in QuickBooks
        $createdCustomer = $dataService->Add($customer);

        return response()->json($createdCustomer);

    }

    public function connect()
    {
        $dataService = $this->getInstanceOfDataService();
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        return redirect()->to($authorizationCodeUrl);
    }


    public function callback(Request $request)
    {
        $inputs = $request->all();
        $dataService = $this->getInstanceOfDataService();
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        /*
            * Update the OAuth2Token
            */
        $accessToken =    $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($inputs['code'], $inputs['realmId']);
        $dataService->updateOAuth2Token($accessToken);
        $this->updateQuickbookToken($accessToken);
    }

    private function updateQuickbookToken($accessToken)
    {

        $quickBook_array = [
            'access_token_key' => $accessToken->getAccessToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'access_token_expires_at' => $accessToken->getAccessTokenExpiresAt(),
            'refresh_token_expires_at' => $accessToken->getRefreshTokenExpiresAt(),
            'access_token_validation_period' => $accessToken->getAccessTokenValidationPeriodInSeconds(),
            'refresh_token_validation_period' => $accessToken->getRefreshTokenValidationPeriodInSeconds(),
            'client_id' => $accessToken->getClientID(),
            'client_secret' => $accessToken->getClientSecret(),
            'real_mid' => $accessToken->getRealmID(),
        ];
        $quickBook = $this->quickBook->newQuery()->first();
        if(!$quickBook)
        {
            $quickBook = $this->quickBook->newInstance();
        }
        $quickBook->fill($quickBook_array);
        $quickBook->save();
    }

    private function createInstanceOfOAuth2AccesToken()
    {
        $quickBook = $this->quickBook->newQuery()->first();
        $accessTokenObject = null;
        if($quickBook)
        {
            $accessTokenObject = new OAuth2AccessToken($quickBook->client_id, $quickBook->client_secret, $quickBook->access_token_key, $quickBook->refresh_token, $quickBook->access_token_expires_at, $quickBook->refresh_token_expires_at);
            $accessTokenObject->setAccessTokenValidationPeriodInSeconds($quickBook->access_token_validation_period);
            $accessTokenObject->setRefreshTokenValidationPeriodInSeconds($quickBook->refresh_token_validation_period);
            $accessTokenObject->setRealmID($quickBook->real_mid);
        }
        return $accessTokenObject;
    }

    public function makeApiCall()
    {

        $accessToken = $this->createInstanceOfOAuth2AccesToken();

        $dataService = $this->getInstanceOfDataService();
        /*
            * Update the OAuth2Token of the dataService object
        */
        $dataService->updateOAuth2Token($accessToken);
        $companyInfo = $dataService->getCompanyInfo();

        return successDataResponse(GENERAL_FETCHED_MESSAGE, $companyInfo);
    }

    private function getInstanceOfDataService()
    {
        $array = array(
            'auth_mode' => QUICKBOOK_auth_mode,
            'ClientID' => QUICKBOOK_client_id,
            'ClientSecret' => QUICKBOOK_client_secret,
            'RedirectURI' => QUICKBOOK_oauth_redirect_uri,
            'scope' => QUICKBOOK_oauth_scope,
            'baseUrl' => QUICKBOOK_SANDBOX_baseUrl
        );

        return DataService::Configure($array);
    }
}
