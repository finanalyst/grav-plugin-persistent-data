# Persistent Data Plugin

The **Persistent Data** Plugin is for [Grav CMS](http://github.com/getgrav/grav). It provides a mechanism to store data associated with an authenticated user and to make that data available via Twig whilst the user is logged in and when the user logs in during another session.

## Installation

Installing the Persistent Data plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install persistent-data

This will install the Persistent Data plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/Persistent Data`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `persistent-data`. You can find these files on [GitHub](https://github.com/finanalyst/grav-plugin-persistent-data) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/persistent-data

> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/persistent-data/persistent-data.yaml` to `user/config/plugins/persistent-data.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
forget_on_logout: false
```

Note that if you use the admin plugin, a file with your configuration, and named persistent-data.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

The default is for user data to be retained even after the user has logged out, and so is set when the user logs back in. If this is not desired, then set `forget_on_logout` to true.

## Usage

A form process is provided and all the data in the form is stored in a file associated with the user. The data for that user is then made available via Twig to other pages.

The ***Form*** process is `userinfo`, and the data can be accessed elsewhere as `{{ userinfo.field }}`

By installing the [**DataManager**](https://github.com/getgrav/grav-plugin-data-manager) admin-panel plugin, the persistent data can be examined from the Admin Panel.

#### Example with explanation

Suppose the following is the content of the file `user/pages/01.start/form.md`:
``` yaml
---
title:  Start page
access:
    site.login: true
process:
    twig: true
cache_enable: false
form:
    name: user-data-collect-form
    fields:
        - name: location
          type: select
          label: Current location
          options:
              home: At home
              office: At office
              gym: At the gym
          default: home
        - name: mood
          type: text
          label: State of mind
    buttons:
         - type: submit
           value: Store data
    process:
            userinfo: true
---

## Data collection page

Current values

location: {{ userinfo.location }}

mood: {{ userinfo.mood }}

```
1. `access: site.login: true` is added to prevent anyone who is not a registered site user from accessing the form or the Twig variable. If there is no authenticated user, then the plugin does nothing.  
The `access: site` frontmatter element could be added to any page in the site. However, if no user is logged in when the page is rendered, submitting data will have no effect.  
Consequently, it is probably best to include the line in the file containing the form.

2. `process: twig: true` is added to the frontmatter so that the `{{ userinfo.location }}` is rendered on the page.   
`process: twig: true` must be set for all pages that want to access twig. This can be done by **Grav** page by page, or set in the site configuration.

1. `cache_enable: false` is added to the front matter so that the form is rendered again using the new Twig variable.

3. Within the **form** `process: userinfo` must be added to the form. When the form is processed, each field of the form is added to the storage variable.  
The data is stored in a yaml file in the `data/persistent` directory. The file is named for the `username` associated with the authenticated user.

4. The example demonstrates how the twig variable is accessed. `userinfo` is the data array, and the fields are the names of the input fields of the form.   
If another form uses the ***userinfo*** process, then the array will be over-written, and the form fields will be the new array fields.  
This default action can be over-ridden using the `update` field (see below).

## Updating persistent data

If the `userinfo` Form process has the field `update: true`, then the data from the form will be used to update the `userinfo` persistent data.

This means that multiple forms can be used to update the data without overwriting previous data by using different fields in different forms.

For example, if we create another page `02.update` with `form.md` and the following content:
```Yaml
---
title: Update Form
form:
    name: update-form
    fields:
        - name: activity
          label: Current activity
          type: text
          default: nothing much
    buttons:
         - type: submit
           value: Amend data
    process:
        userinfo:
            update: true
---
## Data amendment page

Current values

location: {{ userinfo.location }}

mood: {{ userinfo.mood }}

activity: {{ userinfo.activity }}
```
The data from the second form (in this case `activity`) is added to `userinfo`, leaving the previous data untouched.

If multiple forms have the same field names, then the new values of the fields overwrite the persistent data.

## Implementation details

1. The plugin also uses the cache facilities provided by **Grav**. This means that the data is read from file only once following the login session authenticating the user. Or until new data is provided through the1 collection form.
> Note: cache must be enabled for all pages that use the `userinfo` twig variable. If cache is not enabled, then the data will be taken from the storage file.

2. The data is stored at `user/data/persistent` with one yaml file for each user.  
The data is stored in plain text, but access to the directory can only be made by the **Grav** system. Consequently, security should be good.
> Suggestion: If there is a security concern, please detail the issue in the `issues` at github.

## To Do

- [] Possible extensions, depending on user feedback:
    - Change the name of the yaml file containing the persistent data to mask the username.
- [*]  Allow for multiple variables from multiple forms, viz., overcome the limitation where a second form with `userinfo` process will override the whole data array.
