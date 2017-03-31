
=== Secapay Payment Plugin for OpenCart===
Contributors: sci
Tags: ecommerce, payment gateway, opencart, secapay form, sci.ng, secapay.com
Requires at least: 2.3
Tested up to: 2.3.0.2
Stable tag: 0.0.1
License: 

Secapay Form Gateway for accepting payments on your OpenCart Store.

== Description ==

Secapay OpenCart Plugin is the easiest way to start processing online payments. It can take as little as 20 minutes to set up and is by far the quickest way to integrate Secapay.

This payment method is designed to pass transaction details from your website to Secapay to carry out a transaction and redirect users back to your site. Outsourcing your payment processing in this way means that no sensitive data is collected, stored or transferred from your site.

This Plugin allows you to accept Secapay Payments removing the need for you to maintain highly secure encrypted databases, obtain digital certificates and invest in high-level PCI DSS compliance.

== Installation ==

1. Download the latest secapay-opencart plugin release(leave it as a zip file).
2. Upload the entire plugin directory to your OpenCart directory.
3. Register with Secapay.com and create a button to receive payment on your site.
4. You can create a button through your dashboard. If you are trying to
receive money for your business, you’re advised to create a ‘Business button’.
5. Click the code symbol -- ‘<>’ for the button you just generated. You’ll
see something similar to: https://demo.secapay.com/pay?button=1&amount=50. This is a button ID copy it.


== Configuration ==

1. Go to Admin -> Extensions -> Payments and enable Secapay Button Payment.
2. Click on the Edit link.
3. Paste the button ID which was copied from secapay into the button ID field.
4. Leave the remaining fields as Sandbox Mode:No, Geo Zone:All Zones and Status:Enabled.
6. Make sure the status is enabled.
7. Click on Order Status and configure according to the respective fields.
8. Save Changes and your plugin is ready for use.


== Changelog ==

= 0.0.1 =
* Initial Release