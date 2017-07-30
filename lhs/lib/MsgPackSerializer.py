import msgpack
import datetime
import decimal
import pytz


class ExtConvertMeta(type):
    classinfos = []
    codes = {}

    def __new__(self, name, bases, attrs):
        for name, val in attrs.items():
            if name in ['encode', 'decode']:
                attrs[name] = staticmethod(val)

        cls = super(ExtConvertMeta, self).__new__(self, name, bases, attrs)

        if not hasattr(cls, 'code'):
            return cls

        if cls.code in self.codes:
            raise ValueError('Duplicate code %s in %s' % (cls.code, cls))

        self.classinfos.append((cls.classinfo, cls))
        self.codes[cls.code] = cls

        return cls

    @classmethod
    def from_obj(self, obj):
        for t, cls in self.classinfos:
            if isinstance(obj, t):
                return cls

        raise TypeError('Unrecognised type: %r' % obj)

    @classmethod
    def from_code(self, code):
        try:
            return self.codes[code]
        except KeyError:
            raise TypeError('Unrecognised type code: %s' % code)

class ExtConvert(metaclass=ExtConvertMeta):
    pass


class DecimalConvert(ExtConvert):
    code = 0
    classinfo = decimal.Decimal

    encode = lambda obj: obj.as_tuple()
    decode = lambda data: decimal.Decimal(data)


assert datetime.datetime.resolution == datetime.timedelta(microseconds=1)

# Must come before date and time as datetime subclasses both
class DateTimeConvert(ExtConvert):
    code = 1
    classinfo = datetime.datetime

    def encode(obj):
        if obj.tzinfo is None:
            tzinfo = None
        elif hasattr(obj.tzinfo, 'zone'):
            tzinfo = obj.tzinfo.zone
        else:
            tzinfo = obj.tzname()

        return (obj.year, obj.month, obj.day,
                obj.hour, obj.minute, obj.second, obj.microsecond,
                tzinfo)

    def decode(data):
        dt = datetime.datetime(*data[:7])
        tzinfo = data[7]
        if tzinfo is None:
            return dt

        return pytz.timezone(tzinfo).localize(dt)

class DateConvert(ExtConvert):
    code = 2
    classinfo = datetime.date

    encode = lambda obj: (obj.year, obj.month, obj.day)
    decode = lambda data: datetime.date(*data)

class TimeConvert(ExtConvert):
    code = 3
    classinfo = datetime.time

    def encode(obj):
        if obj.tzinfo is not None:
            raise ValueError('Timezones can only be serialized for datetimes')

        return (obj.hour, obj.minute, obj.second, obj.microsecond)

    decode = lambda data: datetime.time(*data)



class MsgPackSerializer(object):
    @classmethod
    def encode_ext(self, obj):
        convert = ExtConvertMeta.from_obj(obj)
        packed = msgpack.packb(convert.encode(obj), use_bin_type=True)
        return msgpack.ExtType(convert.code, packed)

    @classmethod
    def decode_ext(self, code, data):
        convert = ExtConvertMeta.from_code(code)
        unpacked = msgpack.unpackb(data, encoding='utf-8')
        return convert.decode(unpacked)

    @classmethod
    def dumps(self, obj):
        return msgpack.packb(obj, default=self.encode_ext, use_bin_type=True)

    @classmethod
    def loads(self, data):
        return msgpack.unpackb(data, ext_hook=self.decode_ext, encoding='utf-8')


if __name__ == '__main__':

    s = MsgPackSerializer()
    data = {1: b'a', 2: 'a', 3: u'a',
            4: [5, 6],
            #7: (8, 9),
            10: decimal.Decimal('1.23'),
            11: datetime.datetime.now(pytz.timezone('Europe/London')),
            12: datetime.datetime.utcnow(),
            13: datetime.date(1801, 1, 1),
            14: datetime.time(12, 0, 0, 1),
    }
    packed = s.dumps(data)
    print('Packed to %s bytes' % len(packed))
    unpacked = s.loads(packed)
    print(unpacked)
    assert unpacked == data

