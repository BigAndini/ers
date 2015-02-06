
# coding: utf-8

"""
Tests the reachability of the defined pages.

usage:
    python $filename

"""


import secret
import unittest
from selenium import webdriver
import urlparse

class Pages(unittest.TestCase):

    need_basic_auth = True
    routes = {
            '/': 'EJC 2015 Pre Registration',
            '/product': 'EJC 2015 Pre Registration',
            '/participant': 'My Participants',
            '/participant/add': 'Add Participant',
            '/order': 'My Shopping Cart',
            # '/user/login': 'Sign In',
            }

    def setUp(self):
        if self.need_basic_auth:
            profile = webdriver.FirefoxProfile()
            profile.set_preference('network.http.phishy-userpass-length', 255)
        else:
            profile = None
        self.driver = webdriver.Firefox(firefox_profile=profile)

        self.base_url = secret.base_url_template % (secret.username, secret.password)
        # get cookie and use it for further requests
        self.goto(self.base_url)
        cookies = self.driver.get_cookies()
        self.driver.add_cookie(cookies[0])

    def goto(self, route='/'):
        self.driver.get(urlparse.urljoin(self.base_url, route))

    def test_title_in_routes(self):
        """ test that the route contains the expected title """
        driver = self.driver
        for route, expected_title in self.routes.items():
            self.goto(route)
            self.assertIn(expected_title, driver.title)

    def test_add_participant(self):
        driver = self.driver
        # necessary for unknown reason
        self.goto('participant/')
        self.goto('participant/add')
        form = driver.find_element_by_id('Participant')
        elem = driver.find_element_by_name("prename")
        elem.send_keys("Heinz")
        elem = driver.find_element_by_name("surname")
        elem.send_keys("Lehmann")
        elem = driver.find_element_by_name("birthday")
        elem.send_keys("1234")
        elem = driver.find_element_by_name("email")
        elem.send_keys("hat-keine@example.com")
        x = form.submit()
        # TODO assert whatever

    def tearDown(self):
        self.driver.close()

if __name__ == "__main__":
    unittest.main()
