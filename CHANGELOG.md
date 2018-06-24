# v1.2.3
## 24 June 2018
1. [](#enhancement)
    * Add include option that only includes listed fields.
    * Bugfix on file name -> .yaml

# v1.2.2
## 23 June 2018
1. [](#enhancement)
    * persistent data is stored as yaml, so name with yaml extension.
    Allows for the data to be visualised by DataManager plugin.

# v1.2.1
## 16 June 2018
1. [](#bugfix)
    * fix wrong permission on stored file
# v1.2.0
##  04/24/2018

1. [](#new)
    * Initial implementation.
2. [](#update)
    * make sure permission on new user file is correct
3. [](#upgrade)
    * enhance to allow for multiple forms.
4. [](#upgrade 29-04-2018)
    * add configuration variable `forget_on_logout`
    * pick up `login` event `onUserLogout` and delete persistent data file if `forget_on_logout` is true.
