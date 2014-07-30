.. include:: Images.txt

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


List
^^^^

|img-7|

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Page with detail view**
   
   b
         You can use this field if you want to use list- and detail view on
         different pages (Empty for same page)


.. container:: table-row

   a
         **Show fields in frontend**
   
   b
         Select some fields for list view (empty: will show all fields) (note:
         if you want to use your own marker in html file tmpl\_list.html so
         this settings don't matter)


.. container:: table-row

   a
         **ABC list**
   
   b
         Show abc list. Default: you can search for all names beginning with a
         letter. (Change the dropdown if you want to use the abc list with
         another field) (Don't show disables abc list)


.. container:: table-row

   a
         **Add search field(s)**
   
   b
         You can add some search fields if you want. If you use the option
         “Search all”, you can define a search field, which search in multiple
         columns at a time. These columns can be defined via typoscript
         (searchAllFields = company, name, city)


.. container:: table-row

   a
         **Add dropdown for category selection**
   
   b
         If you have chosen more than only one category in the main area, you
         can add a dropdown for a category filter in frontend


.. ###### END~OF~TABLE ######

