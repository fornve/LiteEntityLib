<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US">
<head>
        <title>{if $title}{$title} | {else}Free classifieds posting system for advertising in London - {/if} www.anadvert.co.uk</title>
        <link rel="stylesheet" href="/resources/css/style.css" type="text/css" />
        <link rel="stylesheet" href="/resources/css/blueprint/screen.css" type="text/css" />
        <script type="text/javascript" src="/resources/js/jquery.js"></script>
        <script type="text/javascript" src="/resources/js/common.js"></script>
        <script type="text/javascript" src="http://s7.addthis.com/js/200/addthis_widget.js"></script>
        {if $css}
                {foreach from=$css item=stylesheet}
                        <link rel="stylesheet" href="/resources/css/{$stylesheet}" type="text/css" />
                {/foreach}
        {/if}

        {if $smarty.session.user_notification}
        <script type="text/javascript">
                $(document).ready(function(){ldelim}userNotification('{foreach from=$smarty.session.user_notification item=message}{assign var=notification_type value=$message.type}<p>{$message.text}</p>{/foreach}','{$notification_type}');{rdelim});
        </script>
        {/if}
</head>
<body>
        <div onclick="$('#user_notification').fadeOut('slow')" class="user_notification" id="user_notification" title="User Notification" style="display: none;">
                <div id="user_notification_message">{$user_notification}</div>
        </div>
        <div class="header">
                {if $smarty.session.user}
                <span style="float: right;">
                        Welcome <img src="http://sunforum.co.uk/resources/icons/mini/icon_user.gif" /> {$smarty.session.user->username}
                        (<a href="/User/Logout" title="Logout" style="color: white;"><img src="http://sunforum.co.uk/resources/icons/silk/disconnect.png" />Logout</a>)
                </span>
                {/if}
                <a href="http://www.anadvert.co.uk" style="color: #fff;" title="London advertising board">Anadvert</a> - London Classifieds Board - All Service is <strong>free</strong> {* - {$smarty.session.area->name} {if $smarty.session.area->parent}({$smarty.session.area->parent->name}){/if} <a href="/Area/" class="area_change">Change</a>*}

                <div style="clear: both;"></div>
        </div>
        {*<!-- div onClick="$('#subscribe_menu').show('fast');" ><img src="/resources/images/rss.png" alt="Subscribe to RSS"/></div>
        <div id="subscribe_menu" style="display: none; width: 200px; height: 100px; position: absolute; border: 1px solid silver; background-color: white;">
                <ul>
                        <li><a href="/Services/RSS/this">This page</a></li>
            <li><a href="/Services/RSS/list">Advert List</a></li>
                </ul>
        </div -->*}

        <ul class="menu">
        <li{if $page.0=="Home"} class="selected"{/if} style="background: transparent url('/resources/images/application_home.png' ) 4px 4px no-repeat; padding-left: 24px;"><a href="/" title="Home">Home</a></li>
        <li{if $page.0=="AdvertBrowse"} class="selected"{/if} style="background: transparent url('/resources/images/application_view_list.png') 4px 4px no-repeat; padding-left:24px;"><a href="/Advert/Browse/" title="Advert list">List</a></li>
        <li{if $page.0=="Search"} class="selected"{/if} style="background: transparent url('/resources/images/application_form_magnify.png') 4px 4px no-repeat; padding-left:24px;"><a href="/Search/" title="Search in adverts">Search</a></li>
                <li{if $page.0=="CategoryList"} class="selected"{/if} style="background: transparent url('/resources/images/application_view_tile.png') 4px 4px no-repeat; padding-left: 24px;"><a href="/Index/CategoryList/">Categories</a></li>
{*              <li{if $page.0=="AreaList"} class="selected"{/if}><a href="/Area/">Areas</a></li>
*}
       <li{if $page.0=="AdvertCreate"} class="selected"{/if} style="background: transparent url('/resources/images/application_edit.png') 4px 4px no-repeat; padding-left: 24px;"><a href="/Advert/Create" title="New posting">New</a></li>
                <li{if $page.0=="Blog"} class="selected"{/if} style="float:right; margin-right: 5px;"><a href="/Blog/">Blog</a></li>
        </ul>

        <div class="breadcrumbs">
                <ul>
                        <li><a href="/" title="Home">Home</a></li>
                        {foreach from=$breadcrumbs item=breadcrumb}
                        <li> &gt; <a href="{$breadcrumb.link}" title="{$breadcrumb.name}">{$breadcrumb.name}</a></li>
                        {/foreach}
                </ul>
        </div>

    <div class="content">{$content}</div>
        <div>{*
                <script type="text/javascript">var addthis_pub="fornve";</script>
                <a href="http://www.addthis.com/bookmark.php?v=20" onmouseover="return addthis_open(this, '', '[URL]', '[TITLE]')" onmouseout="addthis_close()" onclick="return addthis_sendto()">
                        <img src="http://s7.addthis.com/static/btn/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0" />
                </a>*}
                <a class="a2a_dd" href="http://www.addtoany.com/share_save"><img src="http://static.addtoany.com/buttons/share_save_256_24.png" width="256" height="24" border="0" alt="Share/Save/Bookmark"/></a><script type="text/javascript">a2a_linkname=document.title;a2a_linkurl=location.href;</script><script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>

        </div>
        {if $smarty.session.visited}
        <div id="visited">
                <h2 class="flag">Visited ads</h2>
                {foreach from=$smarty.session.visited item=advert key=visited}
                        <div class="visited_container">
                                {if $advert->images}
                                <a href="/Advert/View/{$advert->id}/{$advert->title|escape:'url'}" title="{$advert->title}">
                                        <img src="/Index/Image/60x60/{$advert->images.0->filename}" alt="{$advert->title}" />
                                </a>
                                {/if}
                                <a href="/Advert/View/{$advert->id}/{$advert->title|escape:'url'}" title="{$advert->title}">{$advert->title|truncate:25}</a>
                        </div>
                {/foreach}
                <div style="clear: both;"></div>
        </div>
        {/if}

        {if $smarty.session.admin}
                <div class="admin" style="border-top: 1px solid black;">
                        <ul>
                                <li style="display: inline;">
                                        <a href="/Admin/Cleaner/">Clear expired</a>
                                </li>
                                <li style="display: inline;">
                                        <a href="/Admin/CreateCategoryAdsIndex/">Create Category Area Ads Index</a>
                                </li>
                                <li style="display: inline;">
                                        <a href="/Documentation/">Documentation</a>
                                </li>
                        </ul>
                </div>
        {/if}

        <div class="footer">2007 - 2009 <a href="http://www.anadvert.co.uk">www.anadvert.co.uk</a> {if $smarty.session.admin}[ Generated in: {$generated}s, db queries: <span onclick="$('#query_debug').show('fast');">{$entity_query|@count}] </span> <a href="/Admin/Logout/">Admin logout</a><br />{/if}
                <a href="/Page/Tms">Terms of Use &amp; Privacy Policy</a> | <a href="/Contactus/">Contact Us</a>
                <p>Fast and easy ads - we promise to give you best way to place your advertisement as well as search for it.</p>
        </div>

        {if $smarty.session.admin}

                {if $entity_query}
                <div id="query_debug" style="display: none; position: fixed; bottom: 0; left: 0; padding: 5px; border: 1px solid gray; background-color: silver;">
                        <span onclick="$('#query_debug').hide('fast')">Close</span>
                        <ol style="">
                                {foreach from=$entity_query item=query}
                                <li style="font-size: 11px;background-color: mistyrose; margin: 5px 0; padding: 3px;">{$query}</li>
                                {/foreach}
                        </ol>
                </div>
                {/if}

        <div>
                <h2>Colors</h2>


                <div class="color1bg" style="width: 100px; float: left;">Color 1</div>
                <div class="color2bg" style="width: 100px; float: left;">Color 2</div>
                <div class="color3bg" style="width: 100px; float: left;">Color 3</div>
                <div class="color4bg" style="width: 100px; float: left;">Color 4</div>
                <div class="color5bg" style="width: 100px; float: left;">Color 5</div>
                <div class="color6bg" style="width: 100px; float: left;">Color 6</div>
                <div class="color7bg" style="width: 100px; float: left;">Color 7</div>

                <div style="clear: both;"></div>
        </div>
        {/if}

        {if $smarty.const.PRODUCTION}{literal}
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-1892634-1");
pageTracker._trackPageview();
} catch(err) {}</script>
        {/literal}{/if}
</body>
</html>
