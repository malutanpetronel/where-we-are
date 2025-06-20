== Description ==

This plugin allows admins to add areas on the map, then calculate their surface, input a tool and send an email to a customer.

== Installation ==

1. Upload `sugerare-utilaj` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Impachetare ==
gulp build

== Duplicare ==
git remote -v
git remote remove origin
git remote add origin ...

== MYSQL
SELECT * FROM wp_postmeta WHERE meta_key IN ('_ref', '_email');

=== Actualizare optiune platit sau nu
SELECT option_value FROM wp_options WHERE option_name = 'where_we_are_paid';
UPDATE wp_options set option_value = 0  WHERE option_name = 'where_we_are_paid';
