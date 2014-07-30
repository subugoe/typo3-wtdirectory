

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


Available Constants
-------------------

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Constants**
   
   b
         **Explanation**
   
   c
         **Default value**


.. container:: table-row

   a
         template.list
   
   b
         Template File list: HTML-template file for list view (see
         EXT:wt\_directory/templates/tmpl\_list.html for an example)
   
   c
         EXT:wt\_directory/templates/tmpl\_list.html


.. container:: table-row

   a
         template.detail
   
   b
         Template File detail: HTML-template file for detail view (see
         EXT:wt\_directory/templates/tmpl\_detail.html for an example)
   
   c
         EXT:wt\_directory/templates/tmpl\_detail.html


.. container:: table-row

   a
         template.ALLmarker
   
   b
         Template Marker ALL: HTML-template file for an ALL marker (all markers
         in one marker) (see EXT:wt\_directory/templates/tmpl\_detail.html for
         an example)
   
   c
         EXT:wt\_directory/templates/tmpl\_markerall.html


.. container:: table-row

   a
         template.pagebrowser
   
   b
         Template for Pagebrowser: HTML-template file for pagebrowser in
         listview (see EXT:wt\_directory/templates/tmpl\_pagebrowser.html for
         an example)
   
   c
         EXT:wt\_directory/templates/tmpl\_pagebrowser.html


.. container:: table-row

   a
         template.search
   
   b
         Template for Search/Filter: HTML-template file for filter and
         searchboxes above the list (see
         EXT:wt\_directory/templates/tmpl\_search.html for an example)
   
   c
         EXT:wt\_directory/templates/tmpl\_search.html


.. container:: table-row

   a
         template.vcard
   
   b
         Template for vcard: HTML-template file for vcard export (see
         EXT:wt\_directory/templates/tmpl\_vcard.html for an example)
   
   c
         EXT:wt\_directory/templates/tmpl\_vcard.html


.. container:: table-row

   a
         path.ttaddress\_pictures
   
   b
         Relatvie path for tt\_address pictures
   
   c
         uploads/pics/


.. container:: table-row

   a
         enable.hideDescription
   
   b
         Hide description if empty value: Description will not shown if the
         value is empty
   
   c
         1


.. container:: table-row

   a
         enable.googlemapOnDetail
   
   b
         Show on map on detailpage: GOOGLEMAP - If a user changes from list to
         detailview the right marker is shown in the googlemap (if rggooglemap
         is included on detailpage)
   
   c
         0


.. container:: table-row

   a
         enable.vCardForList
   
   b
         vCard export in listview: Enables vCard export possibility in list
         view
   
   c
         0


.. container:: table-row

   a
         enable.vCardForDetail
   
   b
         vCard export in detailview: Enables vCard export possibility in detail
         view
   
   c
         1


.. container:: table-row

   a
         enable.powermailForList
   
   b
         powermail link in listview: Enables powermail link to a page with
         powermail to change the receiver to the current tt\_address email
         address - in list view
   
   c
         0


.. container:: table-row

   a
         enable.powermailForDetail
   
   b
         powermail link in detailview: Enables powermail link to a page with
         powermail to change the receiver to the current tt\_address email
         address - in detail view
   
   c
         1


.. container:: table-row

   a
         morelink\_detail.condition
   
   b
         Condition for more link: Show more link in list view only if one field
         is filled with content (e.g. mobile, fax)
   
   c


.. container:: table-row

   a
         label.vCard
   
   b
         vCard link label: Label for vCard links
   
   c
         <img src="typo3conf/ext/wt\_directory/ext\_icon.gif" alt="vCard icon"
         />


.. container:: table-row

   a
         label.powermail
   
   b
         powermail link label: Label for powermail links
   
   c
         <img src="typo3conf/ext/powermail/ext\_icon.gif" alt="powermail icon"
         />


.. container:: table-row

   a
         list.perPage
   
   b
         Show results per page: Show X results per page in list view
   
   c
         10


.. container:: table-row

   a
         list.orderby
   
   b
         List view - order by: Define order by for the listing of the
         addresses(like last\_name asc)
   
   c


.. container:: table-row

   a
         detail.title
   
   b
         Detailpage title: Define an individual page title (like MY HOMEPAGE
         ###WTDIRECTORY\_TTADDRESS\_NAME###
         ###WTDIRECTORY\_TTADDRESS\_EMAIL###)
   
   c


.. container:: table-row

   a
         detail.emailredirect
   
   b
         Email redirect on detailpage: If this flag is set, outlook (or any
         other email client programm) will be opened with the email of current
         address
   
   c
         0


.. container:: table-row

   a
         wrap.addressgroup
   
   b
         Wrap for addressgroup: If you want to show addressgroups, you can wrap
         each group (if you have more than only 1 per address) (e.g. \|<br />)
   
   c
         \|<br />


.. container:: table-row

   a
         filter.cat.disable
   
   b
         Disable categories in search: Disable some categories in category
         choose (like 45,23,12)
   
   c


.. container:: table-row

   a
         filter.cat.showAllInDropdown
   
   b
         Show all Categories: Show all categories in dropdown in list view.
         Categories may not be selected in flexform (like 0 or 1)
   
   c
         0


.. container:: table-row

   a
         filter.list.clearOldFilter
   
   b
         Clear search filter in singleview: If there is a filter in use and
         than a click to detail and back again, filter is cleared
   
   c
         0


.. container:: table-row

   a
         filter.cat.clearOldFilter
   
   b
         Clear search filter on cat-choose: If there is already set a search
         filter and you change the categories, search filter will be cleaned
   
   c
         0


.. container:: table-row

   a
         vCard.utf8
   
   b
         vCard UTF8 en- or decode: en- or decode for the vCard output
   
   c


.. ###### END~OF~TABLE ######

Constants prefix is always plugin.wtdirectory


