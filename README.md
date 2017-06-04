# Gravity2RDStation

Interface who sends your Gravity Forms Leads to RD Station.

Use example:
```
function do_gform_confirmation($confirmation, $form, $entry, $ajax) {
  $gravity_to_rdstation = new Gravity2RDStation('<your-rdstation-public-token>', '<your-rdstation-public-token>');
  $gravity_to_rdstation->send_lead($form, $entry);
}
add_filter('gform_confirmation', 'do_gform_confirmation', 10, 4);
```
