import requests
import tempfile
import random
import shutil
import subprocess
import os
import os.path
import sys

GROUP = '1183951%40N23'

def save_url(url, dest):
    request = requests.get(url)
    if request.status_code != 200:
        return request.status_code
    dest.write(request.content)
    dest.flush()
    return request.status_code

def photo_url(photo):
    if 'pathalias' in photo and photo['pathalias'] != '':
        user = photo['pathalias']
    else:
        user = photo['owner']
    return "http://www.flickr.com/photos/%s/%s" % (user, photo['id'])

def get_photos(api_key):
    url = ("http://api.flickr.com/services/rest/?method=flickr.groups.pools.getPhotos&api_key=%s&group_id=%s"+
          "&extras=url_s,path_alias&per_page=50&format=json&nojsoncallback=1") % (api_key, GROUP)

    data = requests.get(url).json()
    return data['photos']['photo']

def get_current_path():
    return os.path.dirname(os.path.abspath(__file__))

def pick_photos(photo_list, number_needed, tempdir):
    random.shuffle(photo_list)
    dest_photos = []

    while len(dest_photos) < number_needed:
        photo_data = photo_list.pop()
        photo_id = len(dest_photos)
        dest_filename = "photo_%s.jpg" % photo_id
        dest_path = os.path.join(tempdir, dest_filename)
        with tempfile.NamedTemporaryFile() as temp:
            ret = save_url(photo_data['url_s'], temp)
            if ret != 200:
                # we sometime get 504 errors, just skip the image
                continue
            command = ['convert', '-resize', '120x120^', '-gravity', 'Center', '-crop', '120x120+0+0',
                       '-unsharp', '1', '-quality', '90%', temp.name, dest_path]
            ret = subprocess.call(command)
            if ret != 0:
                print "Call to convert failed with code %s" % ret
                continue
        photo_data['filename'] = dest_filename
        dest_photos.append(photo_data)
    return dest_photos

def render_output(photos, group_id):
    output = ""
    for photo in photos:
        output += ('<a href="%s/in/pool-%s/"><img src="/images/photos/%s" alt="Photo from Flickr"></a>' %
                    (photo_url(photo), group_id, photo['filename']))
    return output

def flickr_fetch():
    source_photos = get_photos(sys.argv[1])
    tempdir = tempfile.mkdtemp()
    os.chmod(tempdir, 0755)
    dest_photos = pick_photos(source_photos, 9, tempdir)
    html = render_output(dest_photos, GROUP)

    current_path = get_current_path()
    dest = os.path.abspath(os.path.join(current_path, '..', 'london.hackspace.org.uk', 'images', 'photos'))
    if os.path.exists(dest):
        shutil.rmtree(dest)
    shutil.move(tempdir, dest)
    dest_html = os.path.abspath(os.path.join(current_path, '..', 'london.hackspace.org.uk', 'flickr.html'))
    with open(dest_html, 'w') as dest_file:
        dest_file.write(html)

if __name__ == '__main__':
    flickr_fetch()
