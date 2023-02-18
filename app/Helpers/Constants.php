<?php

// Statuses
define('DEACTIVE', 0);
define('ACTIVE', 1);
define('PENDING', 2);

// General Success Messages
define('GENERAL_SUCCESS_MESSAGE', 'Data added successfully');
define('GENERAL_SUCCESS', 'Success');
define('GENERAL_UPDATED_MESSAGE', 'Data updated successfully');
define('GENERAL_DELETED_MESSAGE', 'Data deleted successfully');
define('GENERAL_FETCHED_MESSAGE', 'Data fetched successfully');
define('GENERAL_TIME_EXCEED_ERROR', 'Time to verify otp expired');
define('GENERAL_EXCEL_IMPORT_ERROR', 'Data is invalid');


define('GENERAL_ERROR_MESSAGE', 'Operation Failed');

// Reponse Codes
define('SUCCESS_200', 200);
define('ERROR_400', 400);
define('ERROR_401', 401);
define('ERROR_403', 403);
define('ERROR_500', 500);

define('PAGINATE', 15);
define('CUSTOMER_PRODUCT_PAGINATE', 12);
define('RANDOM_PRODUCT_LIMIT', 4);

// Menu Statuses
define('MENU_PENDING', 'Pending');
define('ROLE_ADMIN', 1);
define('ROLE_EDITOR', 2);
define('ROLE_MANAGER', 3);

// Role Actions
define('ROLE_ACTION_READ', 1);
define('ROLE_ACTION_WRITE', 2);

// Vonage Credentials
define('VONAGE_KEY', 'ea1bda72');
define('VONAGE_TOKEN', 'IL5eaAlMbcQvotEf');

// OTP Via
define('EMAIL_OTP', 1);
define('MOBILE_OTP', 1);

// Menu Text Color
define("DARK_TEXT_COLOR", 'Dark');
define("LIGHT_TEXT_COLOR", 'Light');

// Menu Image Style
define("FIT_IMAGE_STYLE", 'Fit');
define("FILL_IMAGE_STYLE", 'Fill');


// Cart Messages
define('CART_EXPIRED_MESSAGE', 'Cart has been expired.');
define('QUANTITY_NOT_AVAILABLE', 'Quantity is not available in stock.');
define('CART_ITEM_TIME_OUT', 'Timeout');

// Admin Product Listing Top Filters
define('ALL_FILTER', 'All');
define('DISABLED_FILTER', 'Disabled');
define('ACTIVE_FILTER', 'Active');
define('NO_MERCHANTS_FILTER', 'No Merchants');
define('NOT_FOR_SALE_FILTER', 'Not for sale');

// Discount Types
define('FLAT_DISCOUNT_TYPE', 'Flat');
define('PERCENTAGE_DISCOUNT_TYPE', 'Percentage');

define('IS_QUANTITY_INCREASED_TRUE', true);

// Product Catalogue
define('MENU_ALREADY_EXISTS_IN_PRODUCT', "Menu already exists in product");


// Quick Book Configrations
define('QUICKBOOK_authorizationRequestUrl', "https://appcenter.intuit.com/connect/oauth2");
define('QUICKBOOK_tokenEndPointUrl', "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer");
define('QUICKBOOK_client_id', "ABp5Ic8mSxX0cNZsZsa9bhzD5nCqTkHbPXubnWR63uvL1IKGzY");
define('QUICKBOOK_client_secret', "zbVdT4w5AGwIm21qbfm6fxOMT2Dac0jTYOm0J1Jp");
define('QUICKBOOK_oauth_scope', "com.intuit.quickbooks.accounting");
define('QUICKBOOK_oauth_redirect_uri', "http://localhost:8000/quickbook/callback");
define('QUICKBOOK_auth_mode', "oauth2");
define('QUICKBOOK_baseUrl', "Development");
define('QUICKBOOK_SANDBOX_baseUrl', "Development");
define('QUICKBOOK_API_TEST_CALL', 'https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365279790430/query');

