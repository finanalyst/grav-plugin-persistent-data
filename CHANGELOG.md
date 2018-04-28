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
