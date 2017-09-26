Choose the name of yout Module.
Module Name Must Be only 1 word AlphaNum.

Extract module.zip into /modules/[The module name choosen]

Example module name is: "mymodule"

Folder Destination is: /modules/mymodules.

Search in all Files and Replace (Case Sensitive) [mod_prototype] With the module name (Full Lower)
Search in all Files and Replace (Case Sensitive) [MOD_PROTOTYPE] With the module name (Full Upper)


Example:
Search in /modules/mymodule/.* 
	search sensitive contents: [mod_prototype]
	replace with: mymodule

Search in /modules/mymodule/.* 
	search sensitive contents: [MOD_PROTOTYPE]
	replace with: MYMODULE

	
Create into DB Tables Module that you need with this prefix:
	cm_mod_[The module name choosen]_*
	
Example:
	cm_mod_mymodule
	cm_mod_mymodule_this_is_my_alternative_table
	cm_mod_mymodule_this_is_my_other_table

