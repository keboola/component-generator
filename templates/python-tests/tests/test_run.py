import filecmp
import unittest
from src.my_component import run


class MyComponentTestCase(unittest.TestCase):
    def test_run_00(self):
        base = '/code/tests/data/00/'
        run(base)
        result = filecmp.cmp(base + "out/tables/destination.csv",
                             base + "_sample_out/tables/destination.csv",
                             False)
        self.assertTrue(result)
