<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
	function get_user_data($field = null, $type = null, $ID = null, $res_data = true) {
		$db = ffDB_Sql::factory();
		
		static $data = array();

		$schema["user"] = array(
			"ID" =>  CM_TABLE_PREFIX . "mod_security_users.ID"
			, "reference" => " IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
							, IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, '', " . CM_TABLE_PREFIX . "mod_security_users.surname) = ''
								, IF(anagraph.ID_type > 0
									, IFNULL(
										(	
											SELECT 
										        IF(ISNULL(GROUP_CONCAT(DISTINCT anagraph_rel_nodes_fields.description
					                                        ORDER BY anagraph_fields.`order_thumb` SEPARATOR ' '))
													OR GROUP_CONCAT(DISTINCT anagraph_rel_nodes_fields.description SEPARATOR '') = ''
													, IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
										            , GROUP_CONCAT(DISTINCT IF(anagraph_rel_nodes_fields.description_text = ''
                                                            , anagraph_rel_nodes_fields.description 
                                                            , anagraph_rel_nodes_fields.description_text
                                                        )    
											            ORDER BY anagraph_fields.`order_thumb` SEPARATOR ' ')
											    ) AS name 
											FROM anagraph_rel_nodes_fields	
											    INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields 
											WHERE anagraph_fields.enable_in_menu > 0
										        AND anagraph_fields.ID_type = anagraph.ID_type
										        AND anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
									    )
									    , IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
									)
									, IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
								) 
								, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname)
							)
							, " . CM_TABLE_PREFIX . "mod_security_users.billreference
						) "
			, "avatar" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.avatar = ''
							, anagraph.avatar
							, " . CM_TABLE_PREFIX . "mod_security_users.avatar
						)"
			, "name" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.name = ''
							, IF(anagraph.name = ''
								, (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                            , anagraph_rel_nodes_fields.description 
                                            , anagraph_rel_nodes_fields.description_text
                                        )    
									FROM anagraph_rel_nodes_fields
									WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
									    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'name')
								)
								, anagraph.name
							)
							, " . CM_TABLE_PREFIX . "mod_security_users.name
						)"
			, "surname" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.surname = ''
							, IF(anagraph.surname = ''
								, (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                            , anagraph_rel_nodes_fields.description 
                                            , anagraph_rel_nodes_fields.description_text
                                        ) 
								    FROM anagraph_rel_nodes_fields
								    WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
								        AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'surname')
								)
								, anagraph.surname
							)
							, " . CM_TABLE_PREFIX . "mod_security_users.surname
						)"
			, "email" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.email = ''
							, IF(anagraph.email = ''
								, (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                            , anagraph_rel_nodes_fields.description 
                                            , anagraph_rel_nodes_fields.description_text
                                        ) 
								    FROM anagraph_rel_nodes_fields
								    WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
								        AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'email')
								)
								, anagraph.email
							)
							, " . CM_TABLE_PREFIX . "mod_security_users.email
						)"
			, "tel" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.tel = ''
							, IF(anagraph.tel = ''
								, (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                            , anagraph_rel_nodes_fields.description 
                                            , anagraph_rel_nodes_fields.description_text
                                        )  
								    FROM anagraph_rel_nodes_fields
								    WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
								        AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'tel')
								)
								, anagraph.tel
							)
							, " . CM_TABLE_PREFIX . "mod_security_users.tel
						)"
			, "billreference" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
							, anagraph.billreference
							, " . CM_TABLE_PREFIX . "mod_security_users.billreference
						)"
			, "billcf" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billcf = ''
							, anagraph.billcf
							, " . CM_TABLE_PREFIX . "mod_security_users.billcf
						)"
			, "billpiva" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billpiva = ''
							, anagraph.billpiva
							, " . CM_TABLE_PREFIX . "mod_security_users.billpiva
						)"
			, "billaddress" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billaddress = ''
							, anagraph.billaddress
							, " . CM_TABLE_PREFIX . "mod_security_users.billaddress
						)"
			, "billcap" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billcap = ''
							, anagraph.billcap
							, " . CM_TABLE_PREFIX . "mod_security_users.billcap
						)"
			, "billprovince" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billprovince = ''
							, anagraph.billprovince
							, " . CM_TABLE_PREFIX . "mod_security_users.billprovince
						)"
			, "billtown" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billtown = ''
							, anagraph.billtown
							, " . CM_TABLE_PREFIX . "mod_security_users.billtown
						)"
			, "billstate" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.billstate > 0
							, (
					            SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
					            FROM
					                " . FF_SUPPORT_PREFIX . "state
					            WHERE " . FF_SUPPORT_PREFIX . "state.ID = " . CM_TABLE_PREFIX . "mod_security_users.billstate                                
					            ORDER BY description
					        )
							, (
					            SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
					            FROM
					                " . FF_SUPPORT_PREFIX . "state
					            WHERE " . FF_SUPPORT_PREFIX . "state.ID = anagraph.billstate                                
					            ORDER BY description
					        )
						)"
			, "shippingreference" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.shippingreference = ''
							, anagraph.shippingreference
							, " . CM_TABLE_PREFIX . "mod_security_users.shippingreference
						)"
			, "shippingaddress" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.shippingaddress = ''
							, anagraph.shippingaddress
							, " . CM_TABLE_PREFIX . "mod_security_users.shippingaddress
						)"
			, "shippingcap" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.shippingcap = ''
							, anagraph.shippingcap
							, " . CM_TABLE_PREFIX . "mod_security_users.shippingcap
						)"
			, "shippingprovince" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.shippingprovince = ''
							, anagraph.shippingprovince
							, " . CM_TABLE_PREFIX . "mod_security_users.shippingprovince
						)"
			, "shippingtown" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.shippingtown = ''
							, anagraph.shippingtown
							, " . CM_TABLE_PREFIX . "mod_security_users.shippingtown
						)"
			, "shippingstate" => "IF(" . CM_TABLE_PREFIX . "mod_security_users.shippingstate > 0
							, (
					            SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
					            FROM
					                " . FF_SUPPORT_PREFIX . "state
					            WHERE " . FF_SUPPORT_PREFIX . "state.ID = " . CM_TABLE_PREFIX . "mod_security_users.shippingstate                                
					            ORDER BY description
					        )
							, (
					            SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
					            FROM
					                " . FF_SUPPORT_PREFIX . "state
					            WHERE " . FF_SUPPORT_PREFIX . "state.ID = anagraph.shippingstate                                
					            ORDER BY description
					        )
						)"
		);
		$schema["anagraph"] = array(
			"ID" =>  "anagraph.ID"
			, "Fname" => "IF(anagraph.ID > 0
								, CONCAT(
									IF(anagraph.billreference = ''
										, IF(CONCAT(anagraph.name, anagraph.surname) = ''
						                    , IF(anagraph.username = ''
						                        , anagraph.email 
						                        , anagraph.username
						                    )
						                    , CONCAT(anagraph.name, ' ', anagraph.surname) 
						                )
										, anagraph.billreference
									)
									, IF(anagraph.uid > 0, ' (online)', '')
								)
								, 'John Doe'
							)"
			, "reference" => "IF(anagraph.ID_type > 0
								, IFNULL(
									(	
										SELECT 
										    IF(ISNULL(GROUP_CONCAT(DISTINCT anagraph_rel_nodes_fields.description
					                                    ORDER BY anagraph_fields.`order_thumb` SEPARATOR ' '))
												OR GROUP_CONCAT(DISTINCT anagraph_rel_nodes_fields.description SEPARATOR '') = ''
												, IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
										        , GROUP_CONCAT(DISTINCT IF(anagraph_rel_nodes_fields.description_text = ''
                                                        , anagraph_rel_nodes_fields.description 
                                                        , anagraph_rel_nodes_fields.description_text
                                                    ) 
											        ORDER BY anagraph_fields.`order_thumb` SEPARATOR ' ')
											) AS name 
										FROM anagraph_rel_nodes_fields	
											INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields 
										WHERE anagraph_fields.enable_in_menu > 0
										    AND anagraph_fields.ID_type = anagraph.ID_type
										    AND anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
									)
									, IF(anagraph.billreference = ''
										, CONCAT(anagraph.name, ' ', anagraph.surname)
										, anagraph.billreference
									)
								)
								, IF(anagraph.billreference = ''
									, CONCAT(anagraph.name, ' ', anagraph.surname)
									, anagraph.billreference
								)
							)"
			, "avatar" => "IF(anagraph.avatar = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'avatar')
                            )
                            , anagraph.avatar
                        )"
			, "name" => "IF(anagraph.name = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'name')
                            )
                            , anagraph.name
                        )"
			, "surname" => "IF(anagraph.surname = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'surname')
                            )
                            , anagraph.surname
                        )"
			, "email" => "IF(anagraph.email = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'email')
                            )
                            , anagraph.email
						)"
			, "tel" => "IF(anagraph.tel = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'tel')
                            )
                            , anagraph.tel
                        )"
			, "billreference" => "IF(anagraph.billreference = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billreference')
                            )
                            , anagraph.billreference
                        )"
			, "billcf" => "IF(anagraph.billcf = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billcf')
                            )
                            , anagraph.billcf
                        )"
			, "billpiva" => "IF(anagraph.billpiva = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billpiva')
                            )
                            , anagraph.billpiva
                        )"
			, "billaddress" => "IF(anagraph.billaddress = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billaddress')
                            )
                            , anagraph.billaddress
                        )"
			, "billcap" => "IF(anagraph.billcap = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billcap')
                            )
                            , anagraph.billcap
                        )"
			, "billprovince" => "IF(anagraph.billprovince = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billprovince')
                            )
                            , anagraph.billprovince
                        )"
			, "billtown" => "IF(anagraph.billtown = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'billtown')
                            )
                            , anagraph.billtown
                        )"
			, "billstate" => "IF(anagraph.billstate > 0
							, (
					            SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
					            FROM
					                " . FF_SUPPORT_PREFIX . "state
					            WHERE " . FF_SUPPORT_PREFIX . "state.ID = anagraph.billstate                                
					            ORDER BY description
					        )
							, ''
						)"
			, "shippingreference" => "IF(anagraph.shippingreference = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'shippingreference')
                            )
                            , anagraph.shippingreference
                        )"
			, "shippingaddress" => "IF(anagraph.shippingaddress = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'shippingaddress')
                            )
                            , anagraph.shippingaddress
                        )"
			, "shippingcap" => "IF(anagraph.shippingcap = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'shippingcap')
                            )
                            , anagraph.shippingcap
                        )"
			, "shippingprovince" => "IF(anagraph.shippingprovince = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'shippingprovince')
                            )
                            , anagraph.shippingprovince
                        )"
			, "shippingtown" => "IF(anagraph.shippingtown = ''
                            , (SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                ) 
                                FROM anagraph_rel_nodes_fields
                                WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields = (SELECT anagraph_fields.ID FROM anagraph_fields WHERE anagraph_fields.name = 'shippingtown')
                            )
                            , anagraph.shippingtown
                        )"
			, "shippingstate" => "IF(anagraph.shippingstate > 0
							, (
					            SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
					            FROM
					                " . FF_SUPPORT_PREFIX . "state
					            WHERE " . FF_SUPPORT_PREFIX . "state.ID = anagraph.shippingstate                                
					            ORDER BY description
					        )
							, ''
						)"
		);		
		if($type === null)
			$type = "user";

		if($res_data) {
			if($ID === null)
				$ID = get_session("UserNID");

			if(!array_key_exists($type, $data) || !array_key_exists($ID, $data[$type])) {
				$str_field = "";
				if(is_array($schema[$type]) && count($schema[$type])) {
					foreach($schema[$type] AS $field_key => $field_sql) {
						if(strlen($str_field))
							$str_field .= " ,";

						$str_field .= $field_sql . " AS " . $field_key;
					}
				}

				if($type == "user") {
					$sSQL = "SELECT 
								$str_field
							FROM " . CM_TABLE_PREFIX . "mod_security_users
								LEFT JOIN anagraph ON anagraph.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
							WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($ID, "Number");

					$sSQL_ext = "SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.* 
								, " . CM_TABLE_PREFIX . "mod_security_users_fields.field AS field_name
							FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
							WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($ID, "Number");
				} elseif($type == "anagraph") {
					$sSQL = "SELECT 
								$str_field
							FROM anagraph
								LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON anagraph.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
							WHERE anagraph.ID = " . $db->toSql($ID, "Number");

					$sSQL_ext = "SELECT anagraph_rel_nodes_fields.* 
								, anagraph_fields.name AS field_name
							FROM anagraph_rel_nodes_fields
								INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields
							WHERE anagraph_rel_nodes_fields.ID_nodes = " . $db->toSql($ID, "Number");
				}
				
				if(strlen($sSQL)) {
					$db->query($sSQL);
					if($db->nextRecord()) {
						$data[$type][$ID]["reference"] = $db->getField("reference", "Text", true);
						$data[$type][$ID]["avatar"] = $db->getField("avatar", "Text", true);
						$data[$type][$ID]["name"] = $db->getField("name", "Text", true);
						$data[$type][$ID]["surname"] = $db->getField("surname", "Text", true);
						$data[$type][$ID]["email"] = $db->getField("email", "Text", true);
						$data[$type][$ID]["tel"] = $db->getField("tel", "Text", true);
						
						$data[$type][$ID]["billreference"] = $db->getField("billreference", "Text", true);
						$data[$type][$ID]["billcf"] = $db->getField("billcf", "Text", true);
						$data[$type][$ID]["billpiva"] = $db->getField("billpiva", "Text", true);
						$data[$type][$ID]["billaddress"] = $db->getField("billaddress", "Text", true);
						$data[$type][$ID]["billcap"] = $db->getField("billcap", "Text", true);
						$data[$type][$ID]["billprovince"] = $db->getField("billprovince", "Text", true);
						$data[$type][$ID]["billtown"] = $db->getField("billtown", "Text", true);
						$data[$type][$ID]["billstate"] = $db->getField("billstate", "Text", true);

						$data[$type][$ID]["shippingreference"] = $db->getField("shippingreference", "Text", true);
						$data[$type][$ID]["shippingaddress"] = $db->getField("shippingaddress", "Text", true);
						$data[$type][$ID]["shippingcap"] = $db->getField("shippingcap", "Text", true);
						$data[$type][$ID]["shippingprovince"] = $db->getField("shippingprovince", "Text", true);
						$data[$type][$ID]["shippingtown"] = $db->getField("shippingtown", "Text", true);
						$data[$type][$ID]["shippingstate"] = $db->getField("shippingstate", "Text", true);
						
						if(strlen($sSQL_ext)) {
							$db->query($sSQL_ext);
							if($db->nextRecord()) {
								do {
									$data[$type][$ID]["custom"][ffCommon_url_rewrite($db->getField("field_name", "Text", true))] = $db->getField("value", "Text", true);
								} while($db->nextRecord());
							}

						}
					}
				}
			}
			
			if(strlen($type)) {
				if($ID > 0) {
					if(strlen($field)) {
						$res = $data[$type][$ID][$field];
					} else {
						$res = $data[$type][$ID];
					}
				} else {
					$res = $data[$type];
				}
			} else {
				$res = $data;
			}
		} else {
			if(strlen($type)) {
				if(strlen($field)) { 
					$res = $schema[$type][$field];
				} else {
					$res = $schema[$type];
				}
			} else {
				$res = $schema;
			}
		}		
		
		return $res;
	}

	function user2anagraph($user_key = null, $type = "user") {
		if(!$user_key)
			$user = get_session("user_permission");

		if(!$user["anagraph"])
		{
			$db = ffDB_Sql::factory();
			$user["anagraph"] = array();

			if(!$user_key && $user["ID"]) {
				$sWhere = "anagraph.uid = " . $db->toSql($user["ID"], "Number");
			} elseif (is_numeric($user_key) && $user_key > 0) {
				$sWhere = "anagraph." . ($type == "user" ? "uid" : "ID") . " = " . $db->toSql($user_key, "Number");
			} elseif (strpos($user_key, "@") !== false) {
				$sWhere = "anagraph.email = " . $db->toSql($user_key);
			} else {
				$sWhere = "anagraph.smart_url = " . $db->toSql($user_key);
			}

			$sSQL = "SELECT anagraph.*
						FROM anagraph
						WHERE " . $sWhere;
			$db->query($sSQL);
			if($db->nextRecord()) {
				$user["anagraph"] = $db->record;

				if($user["anagraph"]["degree"]) {
					$sSQL = "SELECT anagraph_role.*
								FROM anagraph_role
								WHERE anagraph_role.ID = " . $db->toSql($user["anagraph"]["degree"], "Number");
					$db->query($sSQL);
					if($db->nextRecord()) {
						$user["anagraph"]["role"] = $db->getField("name", "Text", true);
					}
				}

				$options = mod_security_get_settings("/");

				$sSQL = "SELECT " . $options["table_groups_name"] . ".gid AS rel_gid
									, " . $options["table_groups_name"] . ".name AS gid_name
							 FROM " . $options["table_groups_rel_user"] . "
								INNER JOIN " . $options["table_groups_name"] . " ON " . $options["table_groups_name"] . ".gid = " . $options["table_groups_rel_user"] . ".gid
									OR " . $options["table_groups_name"] . ".gid = " . $db->toSql($user["anagraph"]["primary_gid"], "Number") . "
							 WHERE " . $options["table_groups_rel_user"] . ".uid = " . $db->toSql($user["anagraph"]["uid"], "Number") . " 
							 ORDER BY " . $options["table_groups_name"] . ".level DESC";
				$db->query($sSQL);
				if ($db->nextRecord())
				{
					$user["anagraph"]["groups"] = array();

					$user["anagraph"]["primary_gid_default"] = $db->getField("rel_gid", "Number", true);
					$user["anagraph"]["primary_gid_default_name"] = $db->getField("gid_name", "Text", true);
					do
					{
						$ID_group = $db->getField("rel_gid", "Number", true);
						$group_name = $db->getField("gid_name", "Text", true);
						if($ID_group > 0)
							$user["anagraph"]["groups"][$group_name] = $ID_group;

						if($user["anagraph"]["primary_gid"] == $ID_group) {
							$user["anagraph"]["primary_gid_name"] = $group_name;

							$user["primary_gid_default"] = $user["anagraph"]["primary_gid"];
							$user["primary_gid_default_name"] = $user["anagraph"]["primary_gid_name"];
						}
					} while($db->nextRecord());

					if(!count($user["anagraph"]["groups"]))
					{
						$user["anagraph"]["groups"][MOD_SEC_GUEST_USER_NAME] = MOD_SEC_GUEST_USER_ID;
						$user["anagraph"]["primary_gid_name"] = MOD_SEC_GUEST_USER_NAME;
					}

					$sSQL = "SELECT " . $options["table_groups_dett_name"] . ".*
								FROM " . $options["table_groups_dett_name"] . "
								WHERE " . $options["table_groups_dett_name"] . ".ID_groups = " . $db->toSql($user["primary_gid"], "Number") . "
								ORDER BY " . $options["table_groups_dett_name"] . ".`order`, " . $options["table_groups_dett_name"] . ".field";
					$db->query($sSQL);
					if($db->nextRecord())
					{
						do {
							$user["anagraph"]["permissions_custom"][$db->getField("field", "Text", true)] = $db->getField("value", "Text", true);
						} while($db->nextRecord());
					}
				}

				if (MOD_SEC_ENABLE_TOKEN && $options["table_token"])
				{
					$user["anagraph"]["token"] = array();
					$sSQL = "SELECT 
								" . $options["table_token"] . ".*
							FROM 
								" . $options["table_token"] . "
							WHERE 
								" . $options["table_token"] . ".ID_user = " . $db->toSql($user["anagraph"]["uid"], "Number") . "
							ORDER BY 
								" . $options["table_token"] . ".`type`";
					$db->query($sSQL);
					if($db->nextRecord())
					{
						do
						{
							$token 		= $db->getField("token", "Text", true);
							$type 		= $db->getField("type", "Text", true);
							$expire 	= $db->getField("expire", "Number", true);
							$status 	= true;
							if($type == "live") {
								$objToken = cache_get_token_file($token);
								$expire = $objToken["expire"];
							}

							$user["anagraph"]["token"][] = array(
								"key" 			=> $token
								, "type" 		=> $type
								, "expire" 		=> $expire
								, "refresh" 	=> $db->getField("refresh_token", "Text", true)
								, "id_remote"	=> $db->getField("ID_remote", "Text", true)
							);
						} while($db->nextRecord());
					}
				}

				if(!$user_key)
					set_session("user_permission", $user);
			}
		}

		return $user["anagraph"];
	}


