<?php
//todo: da estendere
$schema["models"]["person"]         = array(
                                        "fields" => array(
                                            "name"              => "anagraph_person.name"
                                            , "surname"         => "anagraph_person.surname"
                                            , "cel"             => "anagraph_person.cel"
                                            , "gender"          => "anagraph_person.gender"
                                            , "birthday"        => "anagraph_person.birthday"
                                            , "cf"              => "anagraph_person.cf"
                                            , "cv"              => "anagraph_person.cv"
                                            , "short_desc"      => "anagraph_person.short_desc"
                                            , "biography"       => "anagraph_person.biography"
                                        )
                                        , "default" => array(
                                            "name"
                                            , "surname"
                                            , "email"
                                        )
                                    );
$schema["models"]["company"]        = array(
                                        "fields" => array(
                                            "ragsoc"            => "anagraph_company.name"
                                        )
                                        , "default" => array(
                                            "ragsoc"
                                        )
                                    );