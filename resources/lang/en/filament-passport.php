<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client Resource
    |--------------------------------------------------------------------------
    */

    'client' => [
        'sections' => [
            'details' => 'Client Details',
            'usage_instructions' => 'Usage Instructions',
            'endpoints' => 'Endpoints',
        ],

        'fields' => [
            'name' => 'Name',
            'name_help' => 'A human-readable name to identify this OAuth client.',
            'grant_profile' => 'Grant Profile',
            'grant_profile_help' => 'Select the Passport grant flow this client should use. This cannot be changed after creation.',
            'redirect_uris' => 'Redirect URIs',
            'redirect_uris_help' => 'The URLs your application is allowed to redirect to after authorization.',
            'redirect_uri' => 'Redirect URI',
            'redirect_uri_placeholder' => 'https://example.com/auth/callback',
            'add_redirect_uri' => 'Add Redirect URI',
            'provider' => 'Provider',
            'provider_help' => 'The authentication provider this client uses.',
            'confidential' => 'Confidential Client',
            'confidential_help' => 'Confidential clients get a client secret. Public clients do not.',
            'enable_device_flow' => 'Enable Device Authorization Flow',
            'enable_device_flow_help' => 'Allow this authorization code client to also use the device authorization grant.',
        ],

        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'provider' => 'Provider',
            'grant_type' => 'Grant Type',
            'redirect_uris' => 'Redirect URIs',
            'confidential' => 'Confidential',
            'revoked' => 'Revoked',
            'created_at' => 'Created',
        ],

        'grant_profiles' => [
            'authorization_code' => 'Authorization Code',
            'authorization_code_pkce' => 'Authorization Code (PKCE)',
            'device_authorization' => 'Device Authorization',
            'password' => 'Password',
            'implicit' => 'Implicit',
            'client_credentials' => 'Client Credentials',
        ],

        'grant_types' => [
            'personal_access' => 'Personal Access',
            'password' => 'Password',
            'authorization_code' => 'Authorization Code',
            'device_authorization' => 'Device Authorization',
            'implicit' => 'Implicit',
            'client_credentials' => 'Client Credentials',
            'refresh_token' => 'Refresh Token',
        ],

        'filters' => [
            'revoked' => 'Revoked',
            'grant_type' => 'Grant Type',
        ],

        'actions' => [
            'regenerate_secret' => 'Regenerate Secret',
            'regenerate_secret_confirmation' => 'This will generate a new secret and invalidate the current one. Are you sure?',
            'revoke' => 'Revoke',
            'restore' => 'Restore',
            'bulk_revoke' => 'Revoke Selected',
        ],

        'endpoints' => [
            'authorization_url' => 'Authorization URL',
            'token_url' => 'Token URL',
            'device_code_url' => 'Device Code URL',
            'callback_url' => 'Callback URL',
            'copied' => 'Copied!',
        ],

        'usage_instructions' => [
            'authorization_code' => [
                'Redirect the user to /oauth/authorize with response_type=code, client_id, redirect_uri, scope, and state.',
                'Exchange the authorization code at POST /oauth/token with grant_type=authorization_code, client_id, client_secret, redirect_uri, and code.',
                'Use the returned access_token in the Authorization: Bearer header for API requests.',
            ],
            'authorization_code_pkce' => [
                'PKCE clients are public — use client_id and redirect_uri only (no client_secret).',
                'Generate a code_verifier and code_challenge (S256) in your app for each authorization request.',
                'Redirect to /oauth/authorize with response_type=code, client_id, redirect_uri, state, code_challenge, and code_challenge_method=S256.',
                'Exchange at POST /oauth/token with grant_type=authorization_code, client_id, redirect_uri, code, and code_verifier.',
            ],
            'device_authorization' => [
                'Request a device code via POST /oauth/device/code with client_id, scope, and client_secret (if confidential).',
                'Display the verification_uri and user_code to the user so they can authorize on another device.',
                'Poll POST /oauth/token with grant_type=urn:ietf:params:oauth:grant-type:device_code, client_id, device_code, and client_secret (if confidential).',
            ],
            'password' => [
                'Enable the password grant in your AppServiceProvider with Passport::enablePasswordGrant().',
                'Request tokens via POST /oauth/token with grant_type=password, client_id, client_secret (if confidential), username, password, and scope.',
            ],
            'implicit' => [
                'Enable the implicit grant in your AppServiceProvider with Passport::enableImplicitGrant().',
                'Redirect to /oauth/authorize with response_type=token, client_id, redirect_uri, scope, and state.',
                'The access token will be returned directly in the redirect URI fragment.',
            ],
            'client_credentials' => [
                'Request a token via POST /oauth/token with grant_type=client_credentials, client_id, client_secret, and scope.',
                'Protect machine-to-machine routes using the CheckClientCredentials middleware.',
            ],
            'default' => 'Use /oauth/authorize to request authorization and /oauth/token to exchange for access tokens.',
        ],

        'notifications' => [
            'created' => 'Client created successfully.',
            'client_id' => 'Client ID',
            'client_id_copied' => 'Client ID copied',
            'client_secret' => 'Client Secret',
            'client_secret_copied' => 'Client secret copied',
            'redirect_uris_label' => 'Redirect URI(s):',
            'redirect_uris_copied' => 'Redirect URI(s) copied',
            'secret_unavailable' => 'Unavailable in this request',
            'public_client' => 'Public client (no secret)',
            'next_steps' => 'Next steps:',
            'secret_regenerated' => 'New client secret (copy it now, it won\'t be shown again):',
            'revoked' => 'Client has been revoked.',
            'restored' => 'Client has been restored.',
            'bulk_revoked' => 'Selected clients have been revoked.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Token Resource
    |--------------------------------------------------------------------------
    */

    'access_token' => [
        'plural_label' => 'Access Tokens',

        'sections' => [
            'details' => 'Token Details',
        ],

        'columns' => [
            'id' => 'ID',
            'user' => 'User',
            'client' => 'Client',
            'name' => 'Name',
            'scopes' => 'Scopes',
            'revoked' => 'Revoked',
            'created_at' => 'Created',
            'expires_at' => 'Expires',
        ],

        'filters' => [
            'revoked' => 'Revoked',
            'client' => 'Client',
            'expired' => 'Expired',
        ],

        'actions' => [
            'revoke' => 'Revoke',
            'bulk_revoke' => 'Revoke Selected',
        ],

        'notifications' => [
            'token_id_copied' => 'Token ID copied',
            'revoked' => 'Token has been revoked.',
            'bulk_revoked' => 'Selected tokens have been revoked.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Code Resource
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | OAuth Consent Views
    |--------------------------------------------------------------------------
    */

    'oauth' => [
        'authorization_request' => 'Authorization Request',
        'device_authorization_request' => 'Device Authorization Request',
        'requesting_permission' => ':client is requesting permission to access your account.',
        'this_app_will_be_able_to' => 'This application will be able to:',
        'authorize' => 'Authorize',
        'deny' => 'Deny',
        'enter_code' => 'Enter Code',
        'enter_code_description' => 'Enter the code displayed on your device to continue.',
        'user_code' => 'Device Code',
        'user_code_placeholder' => 'ABCD-EFGH',
        'continue' => 'Continue',
    ],

    'auth_code' => [
        'columns' => [
            'id' => 'ID',
            'user' => 'User',
            'client' => 'Client',
            'revoked' => 'Revoked',
            'expires_at' => 'Expires',
        ],

        'filters' => [
            'revoked' => 'Revoked',
        ],

        'actions' => [
            'revoke' => 'Revoke',
        ],

        'notifications' => [
            'revoked' => 'Auth code has been revoked.',
        ],
    ],

];
