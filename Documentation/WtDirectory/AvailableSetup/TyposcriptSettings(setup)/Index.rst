

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Typoscript settings (setup)
^^^^^^^^^^^^^^^^^^^^^^^^^^^

\# WT\_DIRECTORY PLUGIN #

includeLibs.user\_wtdirectory\_pagebrowser =
EXT:wt\_directory/lib/class.user\_wtdirectory\_pagebrowser.php

\# main settings

plugin.tx\_wtdirectory\_pi1 {

\# Constants

template.list = {$plugin.wtdirectory.template.list}

template.detail = {$plugin.wtdirectory.template.detail}

template.ALLmarker = {$plugin.wtdirectory.template.ALLmarker}

template.pagebrowser = {$plugin.wtdirectory.template.pagebrowser}

template.search = {$plugin.wtdirectory.template.search}

template.vcard = {$plugin.wtdirectory.template.vcard}

path.ttaddress\_pictures =
{$plugin.wtdirectory.path.ttaddress\_pictures}

enable.googlemapOnDetail =
{$plugin.wtdirectory.enable.googlemapOnDetail}

enable.hideDescription = {$plugin.wtdirectory.enable.hideDescription}

enable.vCardForList = {$plugin.wtdirectory.enable.vCardForList}

enable.vCardForDetail = {$plugin.wtdirectory.enable.vCardForDetail}

enable.powermailForList =
{$plugin.wtdirectory.enable.powermailForList}

enable.powermailForDetail =
{$plugin.wtdirectory.enable.powermailForDetail}

morelink\_detail.condition =
{$plugin.wtdirectory.morelink\_detail.condition}

label.vCard = {$plugin.wtdirectory.label.vCard}

label.powermail = {$plugin.wtdirectory.label.powermail}

list.perPage = {$plugin.wtdirectory.list.perPage}

list.orderby = {$plugin.wtdirectory.list.orderby}

detail.title = {$plugin.wtdirectory.detail.title}

detail.emailredirect = {$plugin.wtdirectory.detail.emailredirect}

wrap.addressgroup = {$plugin.wtdirectory.wrap.addressgroup}

filter.cat.disable = {$plugin.wtdirectory.filter.cat.disable}

filter.cat.showAllInDropdown =
{$plugin.wtdirectory.filter.cat.showAllInDropdown}

filter.list.clearOldFilter =
{$plugin.wtdirectory.filter.list.clearOldFilter}

filter.cat.clearOldFilter =
{$plugin.wtdirectory.filter.cat.clearOldFilter}

vCard.utf8 = {$plugin.wtdirectory.vCard.utf8}

\# Detail view

detail {

field.tstamp = TEXT

field.tstamp.field = tstamp

field.tstamp.strftime = %d.%m.%Y

field.name = TEXT

field.name.field = name

field.name.wrap = <h2>\|</h2>

field.gender = CASE

field.gender.key.field = gender

field.gender.default = IMAGE

field.gender.default.file = EXT:wt\_directory/files/icon\_female.gif

field.gender.m = IMAGE

field.gender.m.file = EXT:wt\_directory/files/icon\_male.gif

field.first\_name = TEXT

field.first\_name.field = first\_name

field.middle\_name = TEXT

field.middle\_name.field = middle\_name

field.last\_name = TEXT

field.last\_name.field = last\_name

field.birthday = TEXT

field.birthday.field = birthday

field.birthday.strftime = %d.%m.%Y

field.title = TEXT

field.title.field = title

field.email = COA

field.email.if.isTrue.field = email

field.email.10 = IMAGE

field.email.10.file = EXT:wt\_directory/files/icon\_mail.gif

field.email.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_mail"

field.email.20 = TEXT

field.email.20.field = email

field.email.20.typolink.parameter.field = email

field.phone = COA

field.phone.if.isTrue.field = phone

field.phone.10 = IMAGE

field.phone.10.file = EXT:wt\_directory/files/icon\_phone.gif

field.phone.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_phone"

field.phone.20 = TEXT

field.phone.20.field = phone

field.mobile = COA

field.mobile.if.isTrue.field = mobile

field.mobile.10 = IMAGE

field.mobile.10.file = EXT:wt\_directory/files/icon\_cell.gif

field.mobile.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_cell"

field.mobile.20 = TEXT

field.mobile.20.field = mobile

field.www = COA

field.www.if.isTrue.field = www

field.www.10 = IMAGE

field.www.10.file = EXT:wt\_directory/files/icon\_web.gif

field.www.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_www"

field.www.20 = TEXT

field.www.20.field = www

field.www.20.typolink.parameter.field = www

field.www.20.typolink.ATagParams = target="\_blank"

field.address = TEXT

field.address.field = address

field.address.br = 1

field.building = TEXT

field.building.field = building

field.room = TEXT

field.room.field = room

field.company = TEXT

field.company.field = company

field.city = TEXT

field.city.field = city

field.zip = TEXT

field.zip.field = zip

field.region = TEXT

field.region.field = region

field.country = TEXT

field.country.field = country

field.image = TEXT

field.image.field = image

field.image.split {

token = ,

cObjNum = 1

1 {

10 = IMAGE

10.params = class="wt\_directory\_image"

10.file.import.current = 1

10.file.import.dataWrap =
{$plugin.wtdirectory.path.ttaddress\_pictures}

10.file.width = 120

10.imageLinkWrap = 1

10.imageLinkWrap.enable = 1

10.imageLinkWrap {

bodyTag = <body style="background-color: white;">

wrap = <a href="javascript:close();">\|</a>

JSwindow = 1

JSwindow.newWindow = 1

}

}

}

field.fax = COA

field.fax.if.isTrue.field = fax

field.fax.10 = IMAGE

field.fax.10.file = EXT:wt\_directory/files/icon\_fax.gif

field.fax.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_fax"

field.fax.20 = TEXT

field.fax.20.field = fax

field.description = TEXT

field.description.field = description

field.description.br = 1

field.addressgroup = TEXT

field.addressgroup.field = addressgroup

field.anyfield = YOURTYPOSCRIPT

}

\# List view

list {

field.tstamp = TEXT

field.tstamp.field = tstamp

field.tstamp.strftime = %d.%m.%Y

field.name = TEXT

field.name.field = name

field.name.wrap = <h2>\|</h2>

field.gender = CASE

field.gender.key.field = gender

field.gender.default = IMAGE

field.gender.default.file = EXT:wt\_directory/files/icon\_female.gif

field.gender.m = IMAGE

field.gender.m.file = EXT:wt\_directory/files/icon\_male.gif

field.first\_name = TEXT

field.first\_name.field = first\_name

field.middle\_name = TEXT

field.middle\_name.field = middle\_name

field.last\_name = TEXT

field.last\_name.field = last\_name

field.birthday = TEXT

field.birthday.field = birthday

field.birthday.strftime = %d.%m.%Y

field.title = TEXT

field.title.field = title

field.email = COA

field.email.if.isTrue.field = email

field.email.10 = IMAGE

field.email.10.file = EXT:wt\_directory/files/icon\_mail.gif

field.email.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_mail"

field.email.20 = TEXT

field.email.20.field = email

field.email.20.typolink.parameter.field = email

field.phone = COA

field.phone.if.isTrue.field = phone

field.phone.10 = IMAGE

field.phone.10.file = EXT:wt\_directory/files/icon\_phone.gif

field.phone.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_phone"

field.phone.20 = TEXT

field.phone.20.field = phone

field.mobile = COA

field.mobile.if.isTrue.field = mobile

field.mobile.10 = IMAGE

field.mobile.10.file = EXT:wt\_directory/files/icon\_cell.gif

field.mobile.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_cell"

field.mobile.20 = TEXT

field.mobile.20.field = mobile

field.www = COA

field.www.if.isTrue.field = www

field.www.10 = IMAGE

field.www.10.file = EXT:wt\_directory/files/icon\_web.gif

field.www.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_www"

field.www.20 = TEXT

field.www.20.field = www

field.www.20.typolink.parameter.field = www

field.www.20.typolink.ATagParams = target="\_blank"

field.address = TEXT

field.address.field = address

field.address.br = 1

field.building = TEXT

field.building.field = building

field.room = TEXT

field.room.field = room

field.company = TEXT

field.company.field = company

field.city = TEXT

field.city.field = city

field.zip = TEXT

field.zip.field = zip

field.region = TEXT

field.region.field = region

field.country = TEXT

field.country.field = country

field.image = IMAGE

field.image.wrap = \|&nbsp;

field.image.file {

import.dataWrap = {$plugin.wtdirectory.path.ttaddress\_pictures}

import.field = image

import.listNum = 0

width = 120

}

field.fax = COA

field.fax.if.isTrue.field = fax

field.fax.10 = IMAGE

field.fax.10.file = EXT:wt\_directory/files/icon\_fax.gif

field.fax.10.params = style="margin-right: 6px;"
class="wt\_directory\_icon\_fax"

field.fax.20 = TEXT

field.fax.20.field = fax

field.description = TEXT

field.description.field = description

field.description.br = 1

field.addressgroup = TEXT

field.addressgroup.field = addressgroup

field.anyfield = YOURTYPOSCRIPT

}

searchAllFields = company, name, city

\# Pagebrowser

pagebrowser = HMENU

pagebrowser {

special = userfunction

special.userFunc = user\_wtdirectory\_pagebrowser->user\_pagebrowser

1 = TMENU

1 {

wrap = <ul class="wt\_directory\_pagebrowser">\|</ul>

NO.allWrap = <li>\|</li>

ACT = 1

ACT.allWrap = <li>\|</li>

ACT.ATagParams = class="act"

}

}

\# vCard settings

vCard {

### MAIN ###

\# Display name

display\_name = COA

display\_name {

10 = TEXT

10.field = first\_name

10.noTrimWrap = \|\| \|

20 = TEXT

20.field = last\_name

}

\# Firstname

first\_name = TEXT

first\_name.field = first\_name

\# Lastname

last\_name = TEXT

last\_name.field = last\_name

\# Middle name

middle\_name = TEXT

middle\_name.field = middle\_name

\# Title

title = TEXT

title.field = title

\# Name prefix

name\_prefix = TEXT

name\_prefix.field = title

\# Name suffix

name\_suffix = TEXT

name\_suffix.value =

\# Email 1

email1 = TEXT

email1.field = email

\# Email 2

email2 = TEXT

email2.value =

\# Company

company = TEXT

company.field = company

\# Room

company = TEXT

company.room = room

### WORK ###

\# Phone

work\_phone = TEXT

work\_phone.field = phone

\# Post Office Box

work\_po\_box = TEXT

work\_po\_box.value =

\# Extended Address

work\_extended\_address = TEXT

work\_extended\_address.field = address

\# Address

work\_address = TEXT

work\_address.field = address

\# City

work\_city = TEXT

work\_city.field = city

\# State

work\_state = TEXT

work\_state.field = region

\# ZIP

work\_zip = TEXT

work\_zip.field = zip

\# Country

work\_country = TEXT

work\_country.field = country

### PRIVATE ###

\# Phone

home\_phone = TEXT

home\_phone.field = phone

\# Post Office Box

home\_po\_box = TEXT

home\_po\_box.value =

\# Extended Address

home\_extended\_address = TEXT

home\_extended\_address.field = address

\# Address

home\_address = TEXT

home\_address.field = address

\# City

home\_city = TEXT

home\_city.field = city

\# State

home\_state = TEXT

home\_state.field = region

\# ZIP

home\_zip = TEXT

home\_zip.field = zip

\# Country

home\_country = TEXT

home\_country.field = country

### OTHER ###

\# Cellphone

cellphone = TEXT

cellphone.field = mobile

\# Fax

fax = TEXT

fax.field = fax

\# Pager

page = TEXT

page.value =

\# URL

www = TEXT

www.value = Homepage

www.typolink {

parameter.data = field : www

returnLast = url

}

\# Createdate of vCard

crdate = TEXT

crdate.data = date:U

crdate.strftime = %Y-%m-%d %H:%M:%S

\# Birthday

birthday = TEXT

birthday.field = birthday

birthday.strftime = %Y%m%d

\# Role

role = TEXT

role.value =

\# Note

note = TEXT

note.value = created by wt\_directory

\# Timezone

timezone = TEXT

timezone.value = +0100

\# Filename for vCard

filename = COA

filename.wrap = \|.vcf

filename {

10 = TEXT

10.field = first\_name

10.noTrimWrap = \|\|\_\|

20 = TEXT

20.field = last\_name

}

}

}

\# TypeNum 3134 for vCard Download

wtdirectory\_vCard = PAGE

wtdirectory\_vCard {

typeNum = 3134

10 < plugin.tx\_wtdirectory\_pi1

config {

disableAllHeaderCode = 1

disablePrefixComment = 1

xhtml\_cleaning = 0

admPanel = 0

}

}

