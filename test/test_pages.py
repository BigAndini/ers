
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
            '/participant/add': 'Add new Participant',
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

    def test_title_in_routes(self):
        """ test that the route contains the expected title """
        driver = self.driver
        for route, expected_title in self.routes.items():
            driver.get(urlparse.urljoin(self.base_url, route))
            self.assertIn(expected_title, driver.title)

    def test_add_participant(self):
        driver = self.driver
        uri = urlparse.urljoin(self.base_url, 'participant/add')
        driver.get(uri)
        form = driver.find_element_by_id('Participant')
        elem = driver.find_element_by_name("prename")
        elem.send_keys("Heinz")
        elem = driver.find_element_by_name("surname")
        elem.send_keys("Müller")
        elem = driver.find_element_by_name("email")
        elem.send_keys("hat-keine@example.com")
        x = form.submit()
        assert "Hello, World!" in driver.get_page_source()

    def tearDown(self):
        self.driver.close()

if __name__ == "__main__":
    unittest.main()
