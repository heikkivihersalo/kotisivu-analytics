# Getting Started
To get started, set all following options to your.

## Container Settings
**Enable server-side analytics**<br>
Enabling server-side analytics allows plugin to send requests to tag manager server. While disabled plugin still tracks user actions but doesn't send them forward

**Enable Javascript Container**<br>
Enable this option if you want to use client-side (regular Tag Manager) container, alongside server-side container. This option can also be used independently if you just want to use performant alternative to default Tag Manager loading scripts.

**URL**<br>
The url where you want to load client-side javascript. For example google's default `https://www.googletagmanager.com`. Make sure to use actual URL of the server like `https://metrics.kotisivu.dev`

**ID**<br>
ID of client-side tag manager container. Typically something like `GTM-XXXXXX`. Replace `GTM-XXXXXX` with your own ID.

**Timeout**<br>
Sets the delay in milliseconds to delay the tag manager container load. Tag Manager scripts will first wait for consent management systems to load and after that sets a delay to load tag manager scripts.

**Endpoint**<br>
Endpoint where you want to send server-side requests

## Google Analytics 4
**Measurement ID**<br>
Measurement ID for Google Analytics 4. Must be added for the POST request to work correctly!

**Cookie Name**<br>
Sets the cookie name for server-side Google Analytics. By default Google uses a cookie called FPID.

**Cookie Expires**<br>
Cookie expiration time in milliseconds. For more info https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie

**Cookie SameSite**<br>
Cookie samesite rules. For more info read https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite

## Cookie Consent Integration
Kotisivu Analytics integrates with Cookiehub and Cookiebot. You can set the consent settings the way you likeor disable it completely.

**Tracking**<br>
Disabling CMP always loads server-side tracking with cookies. Hybrid tracking always sends http-requests, but does not set cookies without consent. Normal tracking only sents http-requests when user has given consent. **Please note that hybrid tracking is in alpha mode so it is not recommended to use that**

**Provider**<br>
Choose your cookie consent management platform provider.

**Tracking ID**<br>
Set tracking ID for management platform. Cookiehub uses strings like `abc12df` and Cookiebot uses longer codes like `00000-00000-00000-00000-00000`.

## Debugging
**Preview String**<br>
You can get current preview string (X-Gtm-Server-Preview header) from Tag Manager -> Preview -> ... -> Send Requests Manually. Please note that preview string changes after a while so you might need to generate new one quite frequently.