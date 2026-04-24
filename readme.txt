Place a pin for company location on the map.

== Description ==

This plugin allows administrators to mark their company location on a map with a pin.
It also provides the ability to display a company slogan, which can be configured in the plugin's settings area. Additionally, it includes a feature to display a link that guides visitors from their current location to the pinned company location.

The map includes a blinking message at the bottom with a gray-blue color: 'Map created with "Where We Are" plugin.' This message can be removed by purchasing the premium version of the plugin.

Purchasing the plugin supports ongoing development and improvements. Note that this is a one-time payment, and no recurring fees apply.

== Installation ==

1. Upload `where-we-are.zip` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the shortcode `[where_we_are]` to display the map in posts or pages.

NOTE: The unminified source files are located in the `assets/js/src/` folder.

== External Services ==

This plugin connects weekly to an API hosted at [https://shop.webnou.ro/](https://shop.webnou.ro/) to check whether the attribution message should be displayed at the bottom of the map widget.

The map includes a link that allows users to display navigation directions from their current location to the configured pin. Clicking this link will open the user's browser to Google Maps, showing the route from their location to the company location specified in the plugin settings.

== Remove Attribution ==

The message at the bottom of the map: 'Map created with "Where We Are" plugin.' can be removed by purchasing the premium version of the plugin.

To remove the attribution:

1. Navigate to the "We on Map" entry in the WordPress admin menu.
2. Open the 'Unlock' tab and click on 'Remove Attribution.'
3. This action will direct you to [https://shop.webnou.ro/](https://shop.webnou.ro/) with the plugin automatically added to your cart.
4. Complete your order by clicking on "See Cart" and following the checkout process.

When making this purchase, the domain where the plugin is installed will be sent to our servers. This ensures we can verify that your domain has purchased the premium version and can disable the attribution message accordingly.

== Privacy and Terms ==

This service is provided by "AQUIS Grana impex SRL". For more information, please refer to our [Terms of Use](https://shop.webnou.ro/terms-and-conditions/) and [Privacy Policy](https://shop.webnou.ro/privacy-policy/).

== Changelog ==
= 1.5.46 =
* Internal - assets

= 1.5.44 =
* Removed vendor folder

= 1.5.43 =
* No copyVendor for the moment needed

= 1.5.41 =
* Fix copyVendor - composer install --no-dev

= 1.5.40 =
* Added deploy in gulpfile and separated version management in build only

= 1.5.36 =
* Added deploy task and changelog automation
* Added deploy task and changelog automation Fix gitignore for .idea folder

= 1.5.35 =
* Internal BUILD ABORT for plugin  when no new commits.
...
= 1.5.32 =
* Added non minified src for js
* Changed menu position
* Changed prefix for methods
* Initial changes for publishing
* Reset repo - fără istoric
...
= 1.2.0 =
* Added the tabs in the admin area
* Added the readme.txt to prepare the plugin to be published
