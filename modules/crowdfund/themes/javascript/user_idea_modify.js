jQuery(function(){
	jQuery("#IdeaModify_is_innovative").change(function() {
		 if(jQuery(this).children("option:selected").val() > 0) {
			 jQuery(".row.innovative-text").fadeIn();
			 jQuery(".row.innovative-autocertification").fadeIn();
			 jQuery(".row.innovative-documentation").fadeIn();
		 } else { 
			 jQuery(".row.innovative-text").hide();
			 jQuery(".row.innovative-autocertification").hide();
			 jQuery(".row.innovative-documentation").hide();
		 }
	});
        /*
	jQuery("#IdeaModify_is_startup").change(function() {
		if(jQuery(this).children("option:selected").val() != "") {
			if(jQuery(this).children("option:selected").val() > 0) {
				jQuery(".row.capital-funded").hide();
			} else {
				jQuery(".row.capital-funded").fadeIn();				
			}
			jQuery(".row.anagraph-company").fadeIn();
		} else {
	 	  jQuery(".row.anagraph-company").hide();
	 	  jQuery(".row.capital-funded").hide();
		}
	});
	*/
       
       $(document).on('keydown', ".capital-funded INPUT", function() { 
            var value = jQuery(".capital-funded INPUT").val();
           if(value.match(/,00$/))
            {
                value = value.replace(",00","");
                jQuery("#IdeaModify_capital_funded").val(value);
            }
        });
        
        $(document).on('keyup', ".capital-funded INPUT", function() { 
            var value = jQuery(".capital-funded INPUT").val();
            value_string = value.replace(/\./g, "");
            value_number = parseFloat(value_string);
            if(value_number > 0) {
                jQuery("#IdeaModify_capital_funded").val(ff.numberToCurrency(value_number));
            }
        });
	
	jQuery(".row.currency select").change(function(){
		jQuery(".slider-box .symbol").fadeOut(function(){
			if ( jQuery(".row.currency option:selected").attr('value').length)  {
				jQuery(this).html( jQuery(".row.currency option:selected").text() );
				jQuery(this).fadeIn();
			} else { jQuery("this").html( jQuery(".row.currency span").text()).show(); } ; 
		});
	});
	
	

	 if(jQuery(".row.innovative").is("div")) {
	 	jQuery("#IdeaModify_is_innovative").change();
	 }
	 if(jQuery(".row.startup").is("div")) {
	 	jQuery("#IdeaModify_is_startup").change();
	 }
	 
	
	
	jQuery("#IdeaModify").css("min-height", jQuery(".menu-idea").outerHeight() );
	/*
	jQuery(".row.other-cost").after('<div class="row ebitda"><label class="textLabel">EBITDA (F) <br /> (F = A - B - C - D - E)</label><input type="text" class="input" disabled></input> </div>');
	jQuery(".row.depreciation-amortization").after('<div class="row ebit"><label class="textLabel">EBIT (H) <br />(H = F - G)</label><input type="text" class="input" disabled></input> </div>');
	
	if(ff.language == "ITA")
	{
		jQuery(".row.cost-good-service").after('<div class="row first-margin"><label class="textLabel">Primo margine (= A - B)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.finantial-interest").after('<div class="row pretax-profit"><label class="textLabel">Utile lordo (L) <br />(L = H - I)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-depreciation-amortization").after('<div class="row cashflow-operations"><label class="textLabel">Operazioni (C) <br />(C = A + B)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-acquisitions").after('<div class="row cashflow-investing"><label class="textLabel">Investimenti (F) <br />(F = D - E)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-share-issue").after('<div class="row cashflow-financing"><label class="textLabel">Finanziamenti (I) <br />(I = G + H)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-financing").after('<div class="row total-cashflow"><label class="textLabel">Totale (L) <br />(L = C + F + I)</label><input type="text" class="input" disabled></input> </div>');
	};
	if(ff.language == "ENG")  
	{
		jQuery(".row.cost-good-service").after('<div class="row first-margin"><label class="textLabel">Gross margin (= A - B)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.finantial-interest").after('<div class="row pretax-profit"><label class="textLabel">Gross profit (L) <br />(L = H - I)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-depreciation-amortization").after('<div class="row cashflow-operations"><label class="textLabel">Operations (C) <br />(C = A + B)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-acquisitions").after('<div class="row cashflow-investing"><label class="textLabel">Investing (F) <br />(F = D - E)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-share-issue").after('<div class="row cashflow-financing"><label class="textLabel">Financing (I) <br />(I = G + H)</label><input type="text" class="input" disabled></input> </div>');
		jQuery(".row.cashflow-financing").after('<div class="row total-cashflow"><label class="textLabel">Total (L) <br />(L = C + F + I)</label><input type="text" class="input" disabled></input> </div>');
	};
	*/
	jQuery("A-ui-tabs-anchor").click( eachTab() );
	
	
	
	function eachTab() {
		jQuery("#IncomeStatementDetail fieldset").each(function(i){
			valA = parseInt(jQuery(this).children(".row:eq(0) INPUT"));
			valB = parseInt(jQuery(this).children(".row:eq(1) INPUT"));
			grossMargin = valA - valB;
			jQuery(this).children(".row:eq(2) INPUT").attr("value", grossMargin);
			valC = parseInt(jQuery(this).children(".row:eq(3) INPUT"));
			valD = parseInt(jQuery(this).children(".row:eq(4) INPUT"));
			valE = valA - valB - valC- valD;
			jQuery(this).children(".row:eq(5) INPUT").attr("value", valE);
			valF = parseInt(jQuery(this).children(".row:eq(6) INPUT"));
			valG = valE - valF;			
			jQuery(this).children(".row:eq(7) INPUT").attr("value", valG);

			valH = parseInt(jQuery(this).children(".row:eq(8) INPUT"));
			valI = valG - valH;
			jQuery(this).children(".row:eq(9) INPUT").attr("value", valI);

			valL = parseInt(jQuery(this).children(".row:eq(10) INPUT"));
		});
		 
		jQuery("#CashFlowDetail fieldset").each(function(i){
			vallA = parseInt(jQuery("#IncomeStatementDetail fieldset:eq(" + i + ")").find(".row:eq(10) INPUT").attr("value"));
			vallB = jQuery(this).children(".row:eq(0) INPUT");
			vallD = valL + vallB;
			jQuery(this).children(".row:eq(1) INPUT").attr("value", vallD);
			vallE = jQuery(this).children(".row:eq(2) INPUT");
			vallF = jQuery(this).children(".row:eq(3) INPUT");
			vallG = vallE - vallF;
			jQuery(this).children(".row:eq(4) INPUT").attr("value", vallG);
			vallH = jQuery(this).children(".row:eq(5) INPUT");
			vallI = jQuery(this).children(".row:eq(6) INPUT");
			vallL = vallH + vallI;
			jQuery(this).children(".row:eq(7) INPUT").attr("value", vallL);
			vallM = vallD + vallG + vallL;
			jQuery(this).children(".row:eq(8) INPUT").attr("value", vallM);
		});
	}


ff.pluginLoad("help_tip","/modules/crowdfund/themes/javascript/help_tip.js", function() {});
	
	
	
	
	
});

