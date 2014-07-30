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


tt\_news
^^^^^^^^

|img-12|

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Show news contact**
   
   b
         If you want to show a contact person in tt\_news detail view, you can
         check this checkbox.


.. ###### END~OF~TABLE ######

|img-13|

There is a new select box in the tt\_news view. Here you can select
any tt\_address dataset.

You have to add a wt\_directory Frontend Plugin on the page where the
tt\_news detail view is placed. You can select one address in the
wt\_directory address pool (as default address). But when there is a
relation in the current tt\_news, you can show it:

|img-14|

