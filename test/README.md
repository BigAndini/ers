
ERS Tests
=========

These are the tests for the EJC 2015 PreRegistration System.

Setup
-----

Install selenium via easy_install

```
easy_install selenium
```

Install Firefox (CentOS)

```
yum install firefox
```

Testuser
--------

```
cp secret.template.py secret.py
```

Edit `secret.py` and replace USERNAME and PASSWORD with appropiate values.

Usage
-----

```
python test_pages.py
```

If installed, `nose` should also do.

