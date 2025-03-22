# MageOS PageBuilder Templates Import/Export Module for Magento

Enrich PageBuilder adding templates import/export management.

---


## Overview

The **PageBuilder Templates Import/Export** module allows you to import and export pagebuilder templates through different magento instances.
You can use it exporting zip templates file locally and move to remote instances or pulling directly from a configured dropbox storage.


## Features

### Export Template

Once the module is enabled you can export templates through adminhtml ui going to "Content > Elements > Templates" section clicking on the "Actions" column or through cli.
For cli export execute the following command:
```
php bin/magento mage-os:pagebuilder_template:export
```
As result of export you will obtain a .zip file containing the template.


### Import Template

You can import templates from adminhtml ui always from the "Content > Elements > Templates" section.
At the top of the page you'll se a CTA "Import Template", clicking on it a modal opens with an "Upload button" find the zip file from the search window and select it.
Cli command is also supported for import:
```
php bin/magento mage-os:pagebuilder_template:import
```


### Remote Template Import

Once you've configured one or more dropbox apps with success you'll be able to see the remote templates stored there inside the "Import Template" modal.
You will be able to filter them and import on you Magento instance clicking on "Import" link on "Action" column. 
Remote dropbox repositories are synced with configured in every dropbox app webhooks.
An alignment is done once per day at 0:00 but you can also make a full alignment executing the following cli command:
```
php bin/magento mage-os:pagebuilder_template:update-remote-list
```

## Installation

1. Install it into your Mage-OS/Magento 2 project with composer:
    ```
    composer require mage-os/module-pagebuilder-template-import-export
    ```

2. Enable module
    ```
    bin/magento setup:upgrade
    ```

3. Add consumer to queue consumer list (suggested)

   Update your env.php file adding "pbTemplateImport" to consumers. 
   ```
    <?php
    return [
    ...
        'queue' => [
            'consumers_wait_for_messages' => 1
        ],
        'cron_consumers_runner' => [
            'cron_run' => true,
            ...
            'consumers' => [
                ...
                'pbTemplateImport'
                ...
            ]
        ]
    ...
    ];
    ```


## Configuration

The module provides configuration options under **Stores > Configuration > MageOS > Pagebuilder template import/export**


#### General Configuration

- **Enable**: Enables or disables the module. This setting is configurable on global scope.
- **Synch remote templates by cron**:  Let you decide to synchronize remote templates by cron (suggested for large sets of templates stored on remote. See point 3 of installation chapter) or directly after configuration save.
- **Dropbox repositories**: Allows you to specify the Dropbox apps from which to receive templates.


#### Dropbox repositories Configuration

To synchronize remote templates stored on a dropbox repository owned by a vendor you need to add a dropbox app.
Follow these instructions:
- Specify a name
- Specify the app_key
- Specify the app_secret (see https://www.dropbox.com/developers/apps or get them from the dropbox app owner)
- Specify the refresh token 

To synchronize remote templates stored on one of your own dropbox accounts add a dropbox app following these steps:
- Specify a name
- Specify the app_key
- Specify the app_secret (see https://www.dropbox.com/developers/apps and copy them)
- Click on "Generate and authorize refresh token" and follow the instructions to generate the one-time valid "access code"
- Paste "access code" inside the input below the "Refresh Token Generator" column and click the button
- Save the configuration clicking on the main configuration "Save" button
- If no errors where encountered during the saving process you'll be able to see a refresh token generated on the fifth column of the row you configured previously.

Congratulations! This credentials will be used for each dropbox api call to the related app storage.
This configuration is managed as multi-row so you can add multiple dropbox apps related to different repositories simultaneously.


#### Dropbox app Creation

In order to connect Magento with dropbox you need to create a dropbox app related to it: https://www.dropbox.com/developers/reference/getting-started
If you're configuring your own dropbox application for your Dropbox Account you must follow these steps:
1) Select "Scoped Access" on "Choose an API" section
2) Select "Full Dropbox" on "Choose the type of access you need" section
3) Add an app name ex: "My Mage-OS Template Storage"
 

#### Dropbox app Configuration

The most important thing is setting the right permissions (following permissions are required):
- files.metadata.read
- files.content.read

If you are the owner on Dropbox space and you can manage the app directly you can also configure webhooks for realtime notification and sync.
To do so, add your site webhook endpoint inside "Webhook URIs" section as follows:
https://ww.mysite.com/pagebuildertemplateie/template_remote/sync

If correct, your Magento instance will be updated realtime for any update on your Dropbox.

If you're not the owner of the Dropbox you must ask for webhook update to the owner.


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
