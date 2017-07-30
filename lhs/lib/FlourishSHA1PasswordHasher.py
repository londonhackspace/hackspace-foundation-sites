from django.contrib.auth.hashers import BasePasswordHasher, mask_hash
from django.utils.crypto import constant_time_compare, get_random_string
from django.utils.encoding import force_bytes
from django.utils.translation import ugettext_lazy as _

from collections import OrderedDict
import hashlib

class FlourishSHA1PasswordHasher(BasePasswordHasher):
    algorithm = 'flourish_sha1'

    def salt(self):
        return get_random_string(10)

    def encode(self, password, salt):
        assert password is not None
        assert salt and '$' not in salt
        assert len(salt) == 10

        sha1 = hashlib.sha1(force_bytes(salt + password)).hexdigest()
        for i in range(0, 1000, 2):
            sha1 = hashlib.sha1(force_bytes(sha1 + password)).hexdigest()
            sha1 = hashlib.sha1(force_bytes(sha1 + salt)).hexdigest()

        return "%s$%s$%s" % (self.algorithm, salt, sha1)

    def verify(self, password, encoded):
        algorithm, salt, hash = encoded.split('$', 2)
        assert algorithm == self.algorithm
        encoded_2 = self.encode(password, salt)
        print(encoded, encoded_2)
        return constant_time_compare(encoded, encoded_2)

    def safe_summary(self, encoded):
        algorithm, salt, hash = encoded.split('$', 2)
        assert algorithm == self.algorithm
        return OrderedDict([
            (_('algorithm'), algorithm),
            (_('salt'), mask_hash(salt, show=2)),
            (_('hash'), mask_hash(hash)),
        ])

    def harden_runtime(self, password, encoded):
        pass

