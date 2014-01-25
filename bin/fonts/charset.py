basic_latin = range(0x20, 0x7f)

latin_accented  = range(0xc0, 0xd7)
latin_accented += range(0xd8, 0xf7)
latin_accented += range(0xf8, 0x100)
latin_accented += [0x0152] # OElig
latin_accented += [0x0153] # oelig
latin_accented += [0x0104] # LATIN CAPITAL LETTER A WITH OGONEK
latin_accented += [0x0105] # LATIN SMALL LETTER A WITH OGONEK
latin_accented += [0x0106] # LATIN CAPITAL LETTER C WITH ACUTE
latin_accented += [0x0107] # LATIN SMALL LETTER C WITH ACUTE
latin_accented += [0x0118] # LATIN CAPITAL LETTER E WITH OGONEK
latin_accented += [0x0119] # LATIN SMALL LETTER E WITH OGONEK
latin_accented += [0x0141] # LATIN CAPITAL LETTER L WITH STROKE
latin_accented += [0x0142] # LATIN SMALL LETTER L WITH STROKE
latin_accented += [0x0143] # LATIN CAPITAL LETTER N WITH ACUTE
latin_accented += [0x0144] # LATIN SMALL LETTER N WITH ACUTE
latin_accented += [0x015a] # LATIN CAPITAL LETTER S WITH ACUTE
latin_accented += [0x015b] # LATIN SMALL LETTER S WITH ACUTE
latin_accented += [0x0179] # LATIN CAPITAL LETTER Z WITH ACUTE
latin_accented += [0x017a] # LATIN SMALL LETTER Z WITH ACUTE
latin_accented += [0x017b] # LATIN CAPITAL LETTER Z WITH DOT ABOVE
latin_accented += [0x017c] # LATIN SMALL LETTER Z WITH DOT ABOVE
latin_accented += [0x011e] # LATIN CAPITAL LETTER G WITH BREVE
latin_accented += [0x011f] # LATIN SMALL LETTER G WITH BREVE
latin_accented += [0x0130] # LATIN CAPITAL LETTER I WITH DOT ABOVE
latin_accented += [0x0131] # LATIN SMALL LETTER DOTLESS I
latin_accented += [0x015e] # LATIN CAPITAL LETTER S WITH CEDILLA
latin_accented += [0x015f] # LATIN SMALL LETTER S WITH CEDILLA

latin_useful  = [0x00a3] # pound
latin_useful += [0x00a9] # copy
latin_useful += [0x00b5] # micro
latin_useful += [0x00b6] # para
latin_useful += [0x00b7] # middot
latin_useful += [0x2013] # ndash
latin_useful += [0x2014] # mdash
latin_useful += [0x2018] # lsquo
latin_useful += [0x2019] # rsquo
latin_useful += [0x201c] # ldquo
latin_useful += [0x201d] # rdquo
latin_useful += [0x2022] # bull
latin_useful += [0x2026] # hellip
latin_useful += [0x20ac] # euro

# 0x200b and 0xfeff not required
