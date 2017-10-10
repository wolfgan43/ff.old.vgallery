if (!ff.cms) ff.cms = {};
ff.cms.form = (function () {
	
	var that = { /* publics*/
		__init : false
		, error : false
		, "deps": undefined
		, "pricelist": undefined
        , "pricelistCache" : {}
        , "pricelistCacheHash" : {}
		, "discount": undefined
		, "formId" : undefined
                , "insert" : function(component, force_form) {
                    jQuery("#" + component).children().prop('disabled',true);
                    jQuery("#" + component + " .error").remove();
                    if(force_form)
                        jQuery("#" + component + "_ffm").val(jQuery("." + force_form).text());

                    ff.pluginLoad('ff.ajax', '/themes/library/ff/ajax.js', function() { 
                       // document.getElementById('frmAction').value = component + '_insert';  
                        ff.ajax.doRequest({'component' : component, 'action' : component + '_insert'}); 

                        jQuery('body').animate({
                            scrollTop: jQuery("#" + component).offset().top - 100
                        }, 1000);
                    });
                }
		, "init" : function(formId, dynamic, deps, pricelist, discount) { 
			var that = this;

			this.formId = formId;
			this.deps = deps;
			this.pricelist = pricelist;
            if(typeof this.pricelist == "object") {     
                this.pricelistCache = this.pricelist;
            }
			this.discount = discount;

			if(!this.__init) {
				ff.ajax.addEvent({
					"event_name" : "onUpdatedContent",
					"func_name" : function (params, data) {
						ff.cms.form.init(formId, dynamic, deps, pricelist, discount);						
					}
				});
				this.__init = true;
			}

			if(dynamic["label"]) {
				jQuery("#" + formId + " .dynamic-label:not(.slider)").on("keyup", function() {
					var price = jQuery(this).attr("data-price");
					jQuery(this).closest(".row").find(".form-price").text(ff.numberToCurrency(parseFloat(price) * parseFloat(jQuery(this).val())));
				});
				jQuery("#" + formId + " .dynamic-label.slider").each(function() {
					var price = jQuery(this).attr("data-price");
					jQuery("#" + jQuery(this).attr("id").replace("_amount", "_slider")).on("slidechange", function(event, ui) {
						jQuery(this).closest(".row").find(".form-price").text(ff.numberToCurrency(parseFloat(price) * parseFloat(ui.value)));
					});
				});
			}

			if(dynamic["cart"]) {
				this.checkFields();
                                        
				jQuery("#" + formId + " .dynamic-price:not(.slider)" + 
						", #" + formId + " .dynamic-pricelist:not(.slider)" + 
						", #" + formId + "  .dynamic-qta " + 
						", #" + formId + " .dynamic-pfrom" +
						", #" + formId + " .dynamic-value" + 
						", #" + formId + " .dynamic-dep").on("change keyup", function() 
				{
					that.checkFields(this, true);
				});
				
				jQuery("#" + formId + " .dynamic-price.slider" + 
						", #" + formId + " .dynamic-pricelist.slider").each(function() 
				{
					jQuery("#" + jQuery(this).attr("id").replace("_amount", "_slider")).on("slidechange", function(event, ui) {
						that.checkFields(ui.handle, true);
					});
				});				
			}
		}
		, "checkFields" : function(elem, isEvent) {
			var formId = this.formId;
			this.error = false;

			ff.cms.form.checkDeps(elem, isEvent);

			jQuery("#" + formId + " .dynamic-value").each(function() {
				var qta = 0;
				if(jQuery(this).attr("data-from")) {
					var arrValuefrom = jQuery(this).attr("data-from").split("|");

					arrValuefrom.each(function(key, value) {
						qta = qta + parseInt(jQuery("#" + formId + "_" + value).val());
					});
				}
				if(jQuery(this).is("input")) {
					jQuery(this).val(qta);
				} else {
					jQuery(this).next().val(qta);
					jQuery(this).text(qta);
				}
				
			});						
			ff.cms.form.setTotalCart(ff.cms.form.checkPricelist(elem));
		}
		, "checkDeps" : function(elem, isEvent, blockCheckDep) {
			var formId = this.formId;

			if(this.deps) {
				var needCheckDeps = false;
				for(var targetField in this.deps[formId]) {
					var targetElem = "";
					var targetElemVal = "";
					var targetValue = "";

					if(targetField.indexOf(":") >= 0) {
						targetElemVal = "#" + formId + "_" + targetField.split(":")[0];
						targetValue = targetField.split(":")[1];
						
						if(jQuery(targetElemVal).is("input[type=checkbox]")
						|| jQuery(targetElemVal).is("input[type=radio]")
						|| jQuery(targetElemVal).is("input[type=text]")) 
							targetElem = targetElemVal + "[value="  + targetValue+ "]";
						else if(jQuery(targetElemVal).is("select")) {
							targetElem = jQuery(targetElemVal + " option[value='" + targetValue + "']");
						}
					} else {
						targetElemVal = "#" + formId + "_" + targetField;
						targetElem = jQuery(targetElemVal).closest(".row");
					}

					if(isEvent) {
						if(jQuery(targetElem).hasClass("hidden") && !jQuery(targetElemVal).val()) {
							if(jQuery(targetElemVal).attr("data-default") !== undefined ) {
								if(jQuery(elem).attr("id") != jQuery(targetElemVal).attr("id")) {
									if(jQuery(targetElemVal).is("input[type=checkbox]")
										|| jQuery(targetElemVal).is("input[type=radio]")
									) {
										if(jQuery(targetElemVal).attr("data-default") == "1") {
											jQuery(targetElemVal).attr("checked");
										} else {
											jQuery(targetElemVal).removeAttr("checked");
										}
									} else if(
										(jQuery(targetElemVal).is("select") && !jQuery(targetElemVal + " option[value='" + jQuery(targetElemVal).attr("data-default") + "']").hasClass("hidden"))
										|| jQuery(targetElemVal).is("input[type=text]") 
									) {
										jQuery(targetElemVal).val(jQuery(targetElemVal).attr("data-default"));
									}	
									needCheckDeps = true;
								}

								jQuery(targetElemVal).removeAttr("data-default");					
							} else {
								if(jQuery(targetElemVal).is("select")) {
									jQuery(targetElemVal).val(jQuery(targetElemVal + " option:not(.hidden):first").val());
								}
							}
						}
					}

					jQuery(targetElem).removeClass("hidden").show();

					var condition = this.deps[formId][targetField];
					for(conditionField in condition) {
					    if(jQuery("#" + formId + "_" + conditionField).length) {
							if(!jQuery("#" + formId + "_" + conditionField).closest(".row").hasClass("hidden")) {
								for(var opt in condition[conditionField]) {
									var rules = condition[conditionField][opt];
									if(rules) {
										var ruleSuccess = 0;
										rules.each(function(key, value) {
											var conditionValue = "";
											if(jQuery("#" + formId + "_" + conditionField).is("input[type=checkbox]")
												|| jQuery("#" + formId + "_" + conditionField).is("input[type=radio]")
											) {
												if(jQuery("#" + formId + "_" + conditionField).is(":visible:checked"))
													conditionValue = "1";
												else
													conditionValue = "0";
											} else {
												conditionValue = jQuery("#" + formId + "_" + conditionField).val();
											}

											switch(value["op"]) {
												case "==":
													if(conditionValue == value["data"])
														ruleSuccess++;
													break;
												case "<":
													if(parseFloat(conditionValue) < value["data"])
														ruleSuccess++;
													break;
												case ">":
													if(parseFloat(conditionValue) > value["data"])
														ruleSuccess++;
													break;
												case "<=":
													if(parseFloat(conditionValue) <= value["data"])
														ruleSuccess++;
													break;
												case ">=":
													if(parseFloat(conditionValue) >= value["data"])
														ruleSuccess++;
													break;
												case "<>":
													if(conditionValue != value["data"])
														ruleSuccess++;
													break;
												default:
											}
											if(value["limit"] === undefined || value["limit"] == jQuery(targetElemVal).val()) {
											
											}
										});
		
										if(ruleSuccess != rules.length) {
											jQuery(targetElem).addClass("hidden").hide();
										} 
									}
								}
							} else {
								jQuery(targetElem).addClass("hidden").hide();
							}
						}
					}

					if(jQuery(targetElem).hasClass("hidden")) {
						if(jQuery(targetElemVal).is("input[type=checkbox]:checked")
						|| jQuery(targetElemVal).is("input[type=radio]:checked")) {
							jQuery(targetElemVal).attr("data-default", "1");
							jQuery(targetElemVal).removeAttr("checked");
						} else if(jQuery(targetElemVal).is("input[type=text]")) {
							jQuery(targetElemVal).attr("data-default", jQuery(targetElemVal).val());
							jQuery(targetElemVal).val(""); //probabilemnte da sistemare
						} else if(jQuery(targetElemVal).is("select")) {
							if(jQuery(targetElem).hasClass("row")) {
								if(jQuery(targetElemVal).val()) {
									jQuery(targetElemVal).attr("data-default", jQuery(targetElemVal).val());
								}

								jQuery(targetElemVal).val("");
							} else {
								if(jQuery(targetElem).val() == jQuery(targetElemVal).val()) {
									if(jQuery(targetElemVal + " option[value='" + jQuery(targetElemVal).val() + "']").hasClass("hidden")) {
										jQuery(targetElemVal).attr("data-default", jQuery(targetElemVal).val());
									}
								
									if(jQuery(targetElemVal + " option:not(.hidden):first").val() !== undefined) {
										jQuery(targetElemVal).val(jQuery(targetElemVal + " option:not(.hidden):first").val());
									} else {
										jQuery(targetElemVal).val("");
									}
								}
							}
							//if(jQuery(targetElemVal + " option:not(.hidden):first").val() !== undefined) {
								//jQuery(targetElemVal).val(jQuery(targetElemVal + " option[selected]").val());
							//}
						}
						needCheckDeps = true;
					}
					
				}
				if(!blockCheckDep && needCheckDeps)
					this.checkDeps(elem, isEvent, needCheckDeps);
			}
		}
		, "checkPricelist" : function(elem) {
            var that = this;
			var formId = this.formId;
			var total = {
				"price" : 0
				, "weight" : 0
			};
			
			if(this.pricelist) {
				var hashCode = "";
				var hashCodeAlt = "";
				var ph = {
                    "hc" : {}
                    , "hca" : {}
                };

				jQuery("#" + formId + " .dynamic-pricelist:not('DIV')").each(function() {
					var targetVal = "";
                    var targetId = jQuery(this).attr("id").replace(formId + "_", "");
					if(hashCode)
						hashCode += ":";					

					if(hashCodeAlt)
						hashCodeAlt += ":";					

					if(jQuery(this).is("input[type=checkbox]")
						|| jQuery(this).is("input[type=radio]")
					) {
						if(jQuery(this).is(":checked")) {
							targetVal = "1";
						} else {
							targetVal = "0";
						}
					} else {
						targetVal = jQuery(this).val() || "";
					}
					
					hashCode += targetId + "=" + targetVal;

                    ph["hc"][targetId] = targetVal;

					if(jQuery(this).closest(".row").hasClass("hidden")
						|| ((jQuery(this).is("select") && jQuery(this).find("option[value='" + jQuery(this).val() + "']").hasClass("hidden"))
							|| (!jQuery(this).is("select") && jQuery(this).hasClass("hidden"))
						)
					) {
						targetVal = "";
						//targetVal = jQuery(this).attr("data-default") || "";
					}
					
					hashCodeAlt += targetId + "=" + targetVal;  
                    
                    ph["hca"][targetId] = targetVal;

				});
                if(typeof this.pricelist != "object" && !this.pricelistCache[hashCode] && !this.pricelistCacheHash[hashCode] && !this.pricelistCacheHash[hashCodeAlt]) {
                    this.pricelistCacheHash[hashCode] = true;
                    this.pricelistCacheHash[hashCodeAlt] = true;
					jQuery.getJSON("/frame" + window.location.pathname + "?sid="+ this.pricelist +"&ph=" + encodeURIComponent(JSON.stringify(ph)), function(data) {
                        for(var hash in data) {
						    that.pricelistCache[hash] = data[hash];
                        }
                        that.setTotalCart(that.checkPricelistList(elem, hashCode, hashCodeAlt));
                          
					});
                } else 
                    total = this.checkPricelistList(elem, hashCode, hashCodeAlt);
				
			}
			return total; 		
		}
        , "checkPricelistList": function(elem, hashCode, hashCodeAlt) {
        	var that = this;
            var total = {
                "price" : 0
                , "weight" : 0
            };
            var formId = this.formId;
            var pricelistMatch = {};
            var maxPricelistMatch = {};

            if(this.pricelistCache[hashCode]) {
                total["price"] = this.pricelistCache[hashCode]["price"];
                total["weight"] = this.pricelistCache[hashCode]["weight"];                
            } else if(this.pricelistCache[hashCodeAlt]) {
                total["price"] = this.pricelistCache[hashCodeAlt]["price"];
                total["weight"] = this.pricelistCache[hashCodeAlt]["weight"];
            } else {
                for(var ruleID in this.pricelistCache) {
                    var ruleFields = this.pricelistCache[ruleID]["fields"];
                    if(ruleFields) {
                        pricelistMatch[ruleID] = { 
                            "fields" : []
                            , "priority": "normal"
                        };

                        for(var field in ruleFields) {
                            var realField = field.replace("f-", "");
                            var targetElemVal = "#" + formId + "_" + realField + ":visible";
                            var targetVal = "";
                            
                            if(jQuery(targetElemVal).is("input[type=checkbox]")
                                || jQuery(targetElemVal).is("input[type=radio]")
                            ) {
                                if(jQuery(targetElemVal).is(":checked")) {
                                    targetVal = "1";
                                } else {
                                    targetVal = "0";
                                }
                            } else if(jQuery(targetElemVal).is("select")) {
                                targetVal = jQuery(targetElemVal).val();
                            } else {
                                targetVal = jQuery(targetElemVal).val();
                            }

                            if(
                            	targetVal === undefined 
                            	|| (ruleFields[field] && ruleFields[field].indexOf(targetVal) < 0)
                            	|| (ruleFields[field] && ruleFields[field] != targetVal)
                            ) {
                                pricelistMatch[ruleID]["fields"].push(field);
                                if(elem && jQuery(elem).attr("id") == formId + "_" + realField) {
                                    pricelistMatch[ruleID]["priority"] = "low";
                                }
                            }
                        }

                        if(pricelistMatch[ruleID]["fields"].length > 0) {
                            if(!maxPricelistMatch["ruleObj"] 
                                || (maxPricelistMatch["fields"].length > 0 
                                    && (maxPricelistMatch["fields"].length > pricelistMatch[ruleID]["fields"].length
                                        || (maxPricelistMatch["fields"].length == pricelistMatch[ruleID]["fields"].length 
                                            && maxPricelistMatch["priority"] == "low"
                                            && pricelistMatch[ruleID]["priority"] != "low"
                                        )
                                    )
                                )
                            ) {     
                                maxPricelistMatch["ruleObj"] = this.pricelistCache[ruleID];
                                maxPricelistMatch["fields"] = pricelistMatch[ruleID]["fields"];
                                maxPricelistMatch["priority"] = pricelistMatch[ruleID]["priority"];
                                        
                                total["price"] = this.pricelistCache[ruleID]["price"];
                                total["weight"] = this.pricelistCache[ruleID]["weight"];
                            }
                        } else {
                            maxPricelistMatch = undefined;

                            total["price"] = this.pricelistCache[ruleID]["price"];
                            total["weight"] = this.pricelistCache[ruleID]["weight"];
                            break;
                        }
                    }
                }

                if(maxPricelistMatch && maxPricelistMatch["ruleObj"] !== undefined) {
                    maxPricelistMatch["fields"].each(function(key, field) {
                        var realField = field.replace("f-", "");
                        var targetElemVal = "#" + formId + "_" + realField;

                        if(maxPricelistMatch["ruleObj"]["fields"][field]) {
                            if(jQuery(targetElemVal).is("input[type=checkbox]")
                                    || jQuery(targetElemVal).is("input[type=radio]")
                            ) {
                                if(maxPricelistMatch["ruleObj"]["fields"][field] == "1") {
                                    jQuery(targetElemVal).prop("data-default", "1");
                                    jQuery(targetElemVal).prop("checked");
                                } else {
                                    jQuery(targetElemVal).prop("data-default", "0");
                                    jQuery(targetElemVal).removeAttr("checked");
                                }
                            } else {
	                            jQuery(targetElemVal).prop("data-default", jQuery(targetElemVal).val());
	                            if(jQuery(targetElemVal).find("option[value='" + maxPricelistMatch["ruleObj"]["fields"][field] + "']").hasClass("hidden")) {
                                	jQuery(targetElemVal).val("");
                                	that.error = "Missing Pricelist";
								} else {
                                	jQuery(targetElemVal).val(maxPricelistMatch["ruleObj"]["fields"][field]);
								}
                            }
                        } else {
                        	jQuery(targetElemVal).val("");
                        }   
						
						that.checkDeps(elem);
                        
                        if(elem && jQuery(elem).attr("id") == formId + "_" + realField) {
                            if(!jQuery(targetElemVal).next().hasClass("value-no-match")) {
                                jQuery(targetElemVal).after('<span class="value-no-match">Combinazioni non disponibili</span>');
                                setTimeout('jQuery("#' + formId + ' .value-no-match").remove();', 3000);
                            }
                        }

                    });
                }
            }                
            return (this.error ? 0 : total);
        }
		, "getPrice" : function(elem) {
			var total = { 
				"price" : 0 
				, "basic" : 0
                , "nostep": 0
				, "weight" : 0
			};

			if((jQuery(elem).is("input[type=checkbox]") && jQuery(elem).is(":checked"))
				|| (jQuery(elem).is("input[type=radio]") && jQuery(elem).is(":checked"))
				|| (jQuery(elem).is("input[type=text]") && jQuery(elem).val())
				|| (jQuery(elem).is("select") && jQuery(elem).val())
			) {
				var index = undefined;
				if(jQuery(elem).is("select")) {
					if(jQuery(elem).find("option:first").val()) {
						index = jQuery(elem).find("option:selected").index();
					} else {
						index = jQuery(elem).find("option:selected").index() - 1;
					}
				} else if(jQuery(elem).is("input[type=checkbox]")) {
					index = jQuery(elem).parent().index() - 1;
				} else if(jQuery(elem).is("input[type=radio]")) {
					index = jQuery(elem).parent().index();
				}

				var price = jQuery(elem).attr("data-price");
				if(price) {
					if(price.indexOf("|") >= 0) {
						var arrPrice = price.split("|");
						
						if(index >= 0) 
							total["price"] = parseFloat(arrPrice[index]);
					} else {
						total["price"] = parseFloat(price);
					}
				}						

				var priceBasic = jQuery(elem).attr("data-price-basic");
				if(priceBasic) {
					if(priceBasic.indexOf("|") >= 0) {
						var arrPriceBasic = priceBasic.split("|");
						
						if(index >= 0) 
							total["basic"] = parseFloat(arrPriceBasic[index]);
					} else {
						total["basic"] = parseFloat(priceBasic);
					}
				}

                var priceNoStep = jQuery(elem).attr("data-price-nostep");
                if(priceNoStep) {
                    if(priceNoStep.indexOf("|") >= 0) {
                        var arrPriceNoStep = priceNoStep.split("|");
                        
                        if(index >= 0) 
                            total["nostep"] = parseFloat(arrPriceNoStep[index]);
                    } else {
                        total["nostep"] = parseFloat(priceNoStep);
                    }
                }
                                
                var weight = jQuery(elem).attr("data-weight");
                if(weight) {
                    if(weight.indexOf("|") >= 0) {
                        var arrWeight = weight.split("|");
                        
                        if(index >= 0) 
                            total["weight"] = parseFloat(arrWeight[index]);
                    } else {
                        total["weight"] = parseFloat(weight);
                    }
                }
			}
			return total;
		}
		, "setTotalCart" : function(totalPricelist) {
			var formId = this.formId;
			var totalPrice = 0;
			var totalWeight = 0;
			var priceBasic = 0;
            var priceNoStep = 0;
			if(this.error) {
				if(ff.group == "admin") {
					jQuery("#" + formId + " .total-price").text(" - ");
					jQuery("#" + formId + " .total-weight").text(" - ");
				} else {
					jQuery("#" + formId + " .total-price").text(this.error);
					jQuery("#" + formId + " .total-weight").text(this.error);
				}
				
			} else {
				jQuery("#" + formId + " .dynamic-price").each(function() {
					var price = 0;
					var qta = 0;
					var qtaStep = 0;
					var weight = 0;

					if(jQuery(this).attr("data-qfrom")) {
						var arrQtaFrom = jQuery(this).attr("data-qfrom").split("|");
						
						var index = undefined;
						if(jQuery(this).is("select")) {
							if(jQuery(this).find("option:first").val()) {
								index = jQuery(this).find("option:selected").index();
							} else {
								index = jQuery(this).find("option:selected").index() - 1;
							}
						} else if(jQuery(this).is("input[type=checkbox]")) {
							index = jQuery(this).parent().index() - 1;
						} else if(jQuery(this).is("input[type=radio]")) {
							index = jQuery(this).parent().index();
						}
						if(arrQtaFrom[index]) {
							if(arrQtaFrom[index].indexOf(",") >= 0) {
								var arrQtaFromValue = arrQtaFrom[index].split(",");
								arrQtaFromValue.each(function(key, value) {
									qta = qta + parseInt(jQuery("#" + formId + "_" + value).val());
								});
							} else {
								qta = parseInt(jQuery("#" + formId + "_" + arrQtaFrom[index]).val());
							}
						}
					} else if(jQuery(this).is("input[type=text]"))
						qta = parseInt(jQuery(this).val());

				
					var tmpTotal = ff.cms.form.getPrice(this);
					price = tmpTotal["price"];
					priceBasic = priceBasic + tmpTotal["basic"];
	                priceNoStep = priceNoStep + tmpTotal["nostep"];   
					weight = tmpTotal["weight"];

					if(jQuery(this).attr("data-pfrom")) {
						var arrPriceFrom = jQuery(this).attr("data-pfrom").split("|");

						arrPriceFrom.each(function(key, value) {
							var tmpTotal = ff.cms.form.getPrice("#" + formId + "_" + value);

							price = price + tmpTotal["price"];
							priceBasic = priceBasic + tmpTotal["basic"];
	//console.log("pfrom:" + tmpTotal["price"] + "  basic: " + tmpTotal["basic"]);
						});
					}
					
					if(!qta)
						qta = 1;

					if(jQuery(this).attr("data-qstep")) {
					
						var arrQtaStep = jQuery(this).attr("data-qstep").split("|");
						qtaStep = arrQtaStep[index];
						
						if(qtaStep > 0) {
							qta = Math.ceil(qta / qtaStep);
						}
					}								
	//console.log("price: " + price + " qta: " + qta + " basic: " + priceBasic + " total: " + ((price * qta)));

					totalPrice = totalPrice + ((price * qta));	
					totalWeight = totalWeight + ((weight * qta));	
				});
				totalPrice = parseFloat(totalPrice + totalPricelist["price"]);
				totalWeight = parseFloat(totalWeight + totalPricelist["weight"]);

				if(totalPrice > 0) {
					var priceIndex = 1;
					var qta = 1;
					jQuery("#" + formId + " .dynamic-qta").each(function() {
						var actualQta = undefined;

						qta = qta * (parseInt(jQuery(this).val()) || 0);
						
						if(jQuery(this).is("select")) {
							var dataQta = jQuery(this).attr("data-qta");
							var dataPrice = jQuery(this).attr("data-price");

							if(dataQta && dataPrice) {
								dataQta = dataQta.split("|");
								dataPrice = dataPrice.split("|");

								dataQta.each(function(key, value) {
									if(parseInt(value) > qta) {
										actualQta = actualQta;
										return true;
									}

									actualQta = key;
								});
								
								if(actualQta !== undefined)
									priceIndex = priceIndex * parseFloat(dataPrice[actualQta]);
							}
						} else {
							var dataQta = jQuery(this).attr("data-qta");
							var dataPrice = jQuery(this).attr("data-price");

							if(dataQta && dataPrice) {
								dataQta = dataQta.split("|");
								dataPrice = dataPrice.split("|");

								dataQta.each(function(key, value) {
									if(parseInt(value) > qta) {
										actualQta = actualQta;
										return true;
									}

									actualQta = key;
								});
								
								if(actualQta !== undefined)
									priceIndex = priceIndex * parseFloat(dataPrice[actualQta]);
							}
						}
					});
	//console.log("------------------------");
	//console.log(totalPrice + " " + priceNoStep + " " + qta + "  " + priceIndex + "  " + priceBasic + " total: " + (totalPrice + priceNoStep) * priceIndex * qta);   
					totalPrice = ((totalPrice + priceNoStep) * priceIndex * qta) + (priceBasic * priceIndex);
	                totalWeight = (totalWeight * qta);
				}

				jQuery("#" + formId + " .total-price-discount").hide();
				jQuery("#" + formId + " .total-price-discount STRIKE").text("");
				if(this.discount) {
					var totalPriceDiscount = totalPrice;
					if(this.discount["val"] > 0) {
						totalPrice = totalPrice - this.discount["val"];
						if(totalPrice < 0)
							totalPrice = 0;
					}
					if(this.discount["perc"] > 0) {
						totalPrice = Math.$round(totalPrice * (100 - this.discount["perc"]) / 100, 2);
						if(totalPrice < 0)
							totalPrice = 0;
					}
					if(totalPriceDiscount != totalPrice) {
						jQuery("#" + formId + " .total-price-discount STRIKE").text(ff.numberToCurrency(parseFloat(totalPriceDiscount)));
						jQuery("#" + formId + " .total-price-discount").show();
					}
				
				}
				
				
				if(totalPrice > 0) {
					jQuery("#" + formId + " .total-price").removeClass("loading").text(ff.numberToCurrency(parseFloat(totalPrice)));
				} else {
					jQuery("#" + formId + " .total-price").addClass("loading").text("");
				}
				if(totalWeight > 0) {
					jQuery("#" + formId + " .total-weight").removeClass("loading").text(ff.numberToCurrency(parseFloat(totalWeight)));
				} else {
					jQuery("#" + formId + " .total-weight").addClass("loading").text("");
				}
				
			}
		}		
	};

	return that;
})();