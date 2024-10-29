=== Automated PostNord label and pickup - HPOS Supported ===
Contributors: aarsiv
Tags: PostNord shiping, automated, shipping rates, shipping label,  return label
Requires at least: 4.0.1
Tested up to: 6.7
Requires PHP: 5.6
Stable tag: 1.2.2
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

[PostNord shipping](https://wordpress.org/plugins/automated-postnord-shipping/) plugin, integrate the [PostNord Shipping](https://PostNord.ie/) for delivery in Domestic and Internationally. According to the destination, We are providing all kind of PostNord Services. It supports all Countries.

Annoyed of clicking button to create shipping label and generating it here is a hassle free solution, [Shipi](https://myshipi.com) is the tool with fully automated will reduce your cost and will save your time. 

Further, We will track your shipments and will update the order status automatically.

We are providing domestic & international shipping of PostNord

= BACK OFFICE (SHIPPING ): =

[PostNord shipping](https://wordpress.org/plugins/automated-postnord-shipping/) plugin is deeply integrated with [Shipi](https://myshipi.com). So the shipping labels will be generated automatically. You can get the shipping label through email or from the order page.

 This plugin also supported the manual shipments option. By using this you can create the shipments directly from the order page. [Shipi](https://myshipi.com) will keep track of the orders and update the order state to complete.


= Your customer will appreciate : =

* The Product is delivered very quickly. The reason is, there this no delay between the order and shipping label action.
* Access to the many services of PostNord for domestic & international shipping.
* Good impression of the shop.


= HITStacks Action Sample =
[youtube https://www.youtube.com/watch?v=TZei_H5NkyU]


= Informations for Configure plugin =

> If you have already a PostNord Account, please contact PostNord to get your credentials.
> If you are not registered yet, please contact our customer service.
> Functions of the module are available only after receiving your API’s credentials.
> Please note also that phone number registration for the customer on the address webform should be mandatory.
> Create account in hitstacks.

Plugin Tags: <blockquote>PostNord, PostNord SHIPPING, PostNordshipping, PostNordgroup ,PostNord Express shipping, PostNord Woocommerce, PostNord for woocommerce, official PostNord express, PostNord plugin, PostNord shipping plugin, create shipment, shipping plugin, PostNord shipping rates</blockquote>

= Useful filters = 

1) Filter to set service code automated shipments

>add_filter("hitstacks_pn_auto_service", "pn_service_fun", 10, 3);
>function pn_service_fun ($service_code, $rec_con, $ship_con){
>		if($rec_con == $ship_con){
>			$service_code = '30';
>		}else{
>			$service_code = 'UX';
>		}
>	return $service_code;
>}

2) Filter to set service code bulk shipments

>add_filter("hitstacks_pn_bulk_service", "pn_service_bulk_fun", 10, 3);
>function pn_service_bulk_fun ($service_code, $rec_con, $ship_con){
>		if($rec_con == $ship_con){
>			$service_code = '30';
>		}else{
>			$service_code = 'UX';
>		}
>	return $service_code;
>}


= About PostNord =

PostNord AB is the name of the holding company of the two merged postal companies Posten AB and Post Danmark that were officially merged on 24 June 2009. The name of the group was changed 17 May 2011 from Posten Norden to PostNord.The Swedish state is the majority share holder with 60% and the remaining 40% is held by the Danish state.Voting rights are shared equally (50/50)

= About Shipi =

We are Web Development Company. We are planning for make everything automated. 

= What Shipi Tell to Customers? =

> "Configure & take rest"

== Screenshots ==
1. PostNord Account integration settings.
2. Shipper address configuration.
3. Packing algorithm configurations.
4. Shipping services list.
5. Shipping label, tracking, pickup configuration.
6. Order page where you can easily get labels.



== Changelog ==
= 1.2 =
	> Minor improvements
