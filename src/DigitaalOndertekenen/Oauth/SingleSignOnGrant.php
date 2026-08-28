<?php

namespace Koba\DigitaalOndertekenen\Oauth;

use League\OAuth2\Client\Grant\AbstractGrant;

/**
 * The second Digitaal Ondertekenen authentication step is exposed as a
 * provider-specific exchange rather than an RFC 6749 grant. This grant gives
 * it a name in League's request pipeline; the option provider below maps the
 * request to the API's actual JSON contract.
 */
class SingleSignOnGrant extends AbstractGrant
{
    protected function getName()
    {
        return 'single_sign_on';
    }

    /**
     * @return list<string>
     */
    protected function getRequiredRequestParameters()
    {
        return ['token', 'profile_name'];
    }
}
