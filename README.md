# Persistent Data Plugin

The **Persistent Data** Plugin is for [Grav CMS](http://github.com/getgrav/grav). It provides a mechanism to store data associated with an authenticated user and to make that data available via Twig whilst the user is logged in and when the user logs in during another session.

## Installation

Installing the Persistent Data plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install persistent-data

This will install the Persistent Data plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/Persistent Data`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `persistent-data`. You can find these files on [GitHub](https://github.com/r/grav-plugin-persistent-data) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/persistent-data

> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/persistent-data/persistent-data.yaml` to `user/config/plugins/persistent-data.yaml` and only edit that copy.

It is also necessary for there to be a folder `<root>/user/data/persistent` and for it to have the standard **Grav** permissions.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

Note that if you use the admin plugin, a file with your configuration, and named persistent-data.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

## Usage

A form process is provided and all the data in the form is stored in a file associated with the user. The data for that user is then made available via Twig to other pages.

The ***Form*** process is `userinfo`, and the data can be accessed elsewhere as `{{ userinfo.field }}`

#### Example with explanation

Suppose the following is the content of the file `user/pages/01.start/form.md`:
``` yaml
---
title:  Start page
access:
    site.login: true
process:
    twig: true
form:
    name: user-data-collect-form
    fields:
        - name: location
          type: select
          label: Current location
          options:
              - home: At home
              - office: At office
              - gym: At the gym
          default: home
       - name: mood
         type: text
         label: State of mind
    buttons:
     - type: submit
       value: Store data
    process:
        userinfo: true
        redirect: next
---

## Data collection page

Current values

location: {{userinfo.location}}

mood: {{ userinfo.mood }}
```
1. `access: site.login: true` is added to prevent anyone who is not a registered site user from accessing the form or the Twig variable. If there is no authenticated user, then the plugin does nothing.  
The `access: site` frontmatter element could be added to any page in the site. However, if no user is logged in when the page is rendered, submitting data will have no effect.  
Consequently, it is probably best to include the line in the file containing the form.

2. `process: twig: true` is added to the frontmatter so that the `{{ userinfo.location }}` is rendered on the page.   
`process: twig: true` must be set for all pages that want to access twig. This can be done by **Grav** page by page, or set in the site configuration.

3. Within the **form** `process: userinfo` must be added to the form. When the form is processed, each field of the form is added to the storage variable.  
The data is stored in a yaml file in the `data/persistent` directory. The file is named for the `username` associated with the authenticated user.

4. The example demonstrates how the twig variable is accessed. `userinfo` is the data array, and the fields are the names of the input fields of the form.   
If another form uses the ***userinfo*** process, then the array will be over-written, and the form fields will be the array fields.

## Implementation details

1. The plugin also uses the cache facilities provided by **Grav**. This means that the data is read from file only once following the login session authenticating the user. Or until new data is provided through the collection form.
> Note: cache must be enabled for all pages that use the `userinfo` twig variable. If cache is not enabled, then the data will be taken from the storage file.

2. The data is stored at `user/data/persistent` with one yaml file for each user.  
The data is stored in plain text, but access to the directory can only be made by the **Grav** system. Consequently, security should be good.
> Suggestion: If there is a security concern, please detail the issue in the `issues` at github.

## To Do

- [ ] Possible extensions, if found necessary:
    - Change the name of the yaml file containing the persistent data to a hash of the username, rather than the plain username.
    - Allow for multiple variables from multiple forms.
