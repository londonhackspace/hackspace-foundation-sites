#!/usr/bin/env python

import fontforge
from subprocess import Popen, PIPE
import os
from charset import basic_latin, latin_accented, latin_useful
import zlib
import base64
from lxml import etree

def generate_fonts(infile, outbase, outtypes):

    """
    Defaults (not all of these apply to all types):

    [ ] Round                   round
    [x] Hints                   !no-hints
        [x] Flex Hints          !no-flex
    [ ] Output AFM              afm
        [ ] Composites in AFM   composites-in-afm
    [ ] Output PFM              pfm
    [ ] Output TFM & ENC        tfm
    [x] TrueType Hints          !omit-instructions
    [ ] PS Glyph Names          !short-post
    [ ] Apple                   apple
    [x] OpenType                opentype
        [ ] Old style 'kern'    old-kern
        [ ] Dummy 'DSIG'        dummy-dsig
    [ ] Output Glyph Map        glyph-map-file
    [ ] Output OFM & CFG        ofm
    PfaEditTable
        [ ] Save Comments       PfEd-comments
        [ ] Save Colours        PfEd-colors
        [ ] Lookup Names        PfEd-lookups
        [ ] Save Guides         PfEd-guidelines
        [ ] Save Layers         PfEd-background
    [ ] TeX Table               TeX-table
    [ ] Output Font Log
    Save as TypeType (Symbol)   symbol

    https://github.com/fontforge/fontforge/blob/master/fontforge/python.c#L15519-L15544
    https://github.com/fontforge/fontforge/blob/master/fontforge/savefont.c#L1113-L1198
    https://github.com/fontforge/fontforge/blob/master/fontforge/splinefont.h#L1952-L1985
    https://github.com/fontforge/fontforge/blob/master/fontforge/savefontdlg.c#L442-L523

    Some handy scripts:
    http://googlefontdirectory.googlecode.com/hg/tools/
    https://gist.github.com/kevinoid/4029594
    """

    infont = fontforge.open(infile)

    for char in basic_latin + latin_accented + latin_useful:
        if char not in infont:
            print '0x%x (%s) not in font' % (char, unichr(char))
            continue

        infont.selection.select(('more', 'unicode'), char)
        for ref in infont[char].references:
            infont.selection.select(('more',), ref[0])

    infont.selection.invert()
    infont.cut()
    infont.os2_fstype = 0

    for outtype in outtypes:
        outfile = '%s.%s' % (outbase, outtype)
        print 'Generating %s' % outfile
        tempfile = '%s.tmp.%s' % (outbase, outtype)
        outtype = outtype.lower()

        flags = ('opentype', 'short-post')
        infont.generate(tempfile, flags=flags)

        data = open(tempfile).read()
        os.remove(tempfile)
        f = open(outfile, 'wb')

        if outtype == 'svg':
            p = etree.XMLParser(remove_blank_text=True)
            root = etree.XML(data, parser=p)

            # Wrap inside an element with the right default namespace
            svgns = 'http://www.w3.org/2000/svg'
            newroot = root.makeelement('{%s}svg' % svgns, nsmap={None: svgns})
            newroot.append(root)

            # Strip out the fontforge generation info
            metadata = root.xpath('metadata')[0]
            lines = metadata.text.splitlines()
            metadata.text = '\n'.join(lines[3:])

            f.write(etree.tostring(root))

        elif outtype == 'eot':
            p = Popen(['ttf2eot'], shell=True, stdin=PIPE, stdout=PIPE, stderr=PIPE)
            out, err = p.communicate(data)

            f.write(out)

        else:
            f.write(data)

        f.close()
        uncompressed  = open(outfile).read()
        compressed = zlib.compress(uncompressed, 9)
        b64compressed = zlib.compress(base64.b64encode(uncompressed), 9)
        print '%8s %8s %s' % (len(compressed), len(b64compressed), outfile)

if __name__ == '__main__':
    import sys
    infile, outbase = sys.argv[1:3]
    outtypes = sys.argv[3:]
    generate_fonts(infile, outbase, outtypes)

