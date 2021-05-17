# Readme

You will need to deploy two sites i.e local and remote.

Ensure to add the following to the settings.php file for the with the apropriate domain names

```
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];
```

Once the are deployed login and go to admin the configuration

/admin/config/mcc/mccconfig


## Deploy changes

Go to the admin area on the local site :

/admin/config/mccserver/mccrequestremotechanges

Click on the button request changes


To view Changes click on the Request Progress button or

To view the Progress go to the admin area  on the local site :

/admin/config/mccserver/mccconfirmchanges


## Update changes on remote site 

go to the admin area on the Remote Site

/admin/config/system/cron

and click the button run cron
