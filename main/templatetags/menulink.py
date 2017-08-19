from django import template
from django.core import urlresolvers
from django.utils.html import format_html

register = template.Library()

@register.simple_tag(takes_context=True)
def menulink(context, name, title, viewname, *args, **kwargs):
    if context['page'] == name:
        return format_html('<li class="active">{}</li>', title)
    else:
        path = urlresolvers.reverse(viewname, args=args, kwargs=kwargs)
        return format_html('<li><a href="{}">{}</a></li>', path, title)


class PageNameNode(template.Node):
    """
    We need to rework the menu system, but for now this allows us to
    reproduce the same per-template page name functionality from PHP
    """
    def __init__(self, page):
        self.page = page

    def render(self, context):
        context['page'] = self.page
        return ''

def do_page_name(parser, token):
    try:
        # split_contents() knows not to split quoted strings.
        tag_name, format_string = token.split_contents()
    except ValueError:
        raise template.TemplateSyntaxError(
            "%r tag requires a single argument" % token.contents.split()[0]
        )
    if not (format_string[0] == format_string[-1] and format_string[0] in ('"', "'")):
        raise template.TemplateSyntaxError(
            "%r tag's argument should be in quotes" % tag_name
        )
    return PageNameNode(format_string[1:-1])

register.tag('page', do_page_name)


