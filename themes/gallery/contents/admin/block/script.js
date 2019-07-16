/*
 * VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @package VGallery
 *  @subpackage core
 *  @author Alessandro Stucchi <wolfgan@gmail.com>
 *  @copyright Copyright (c) 2004, Alessandro Stucchi
 *  @license http://opensource.org/licenses/gpl-3.0.html
 *  @link https://github.com/wolfgan43/vgallery
 */
$(document).ready(function(){
    /* dichiaro le variabili prima di assegnarne qualsiasi valore */
    var elLenght;
    var elPos;

    $(".eltype-container a").click(function(e){

        /* previene il comportamento default della a */
        e.preventDefault;







        if ($(this).attr("data-attribute") != undefined ){

            $(".content-all>div").removeClass('eltype-visible');
            $(".element-plus").show();
            $(".zero-results").hide();


            var selComp = $(this).attr('data-attribute');

            $(".content-all>div." + selComp).addClass('eltype-visible');




        }



        else {

            $(".content-all>div").addClass('eltype-visible');
            $(".element-plus").hide();

            if ($(".elsearch-input").val().length > 2) {
                var resFound = 0;
                $(".element").each(function(){
                    if ($(this).find(".eltitle").html().toLowerCase().indexOf(searchText) >= 0 || $(this).find(".paragraph").html().toLowerCase().indexOf(searchText) >= 0){
                        $(this).show();
                        resFound++;
                    }

                });

                if (resFound == 0) {
                    $(".zero-results").show();
                }


            }

        }










        /* assegno alla prima variabile la larghezza ESTERNA dell'elemento cliccato*/
        elLenght = $(this).outerWidth();

        /* assegno alla sottolineatura la variabile qua sopra */
        $(".eltype-underline").width(elLenght);


        /* assegno alla seconda variabile la differenza tra
        la posizione sinistra del contenitore
        la posizione sinistra dell'elemento cliccato
        (in questo modo ottengo il posizionamento dell'elemento cliccato relativo al suo contenitore)
        '*/
        elPos = $(this).offset().left - $(this).parent().offset().left + "px";

        /* assegno alla sottolineatura il valore della variabile qua sopra */
        $(".eltype-underline").css("left", elPos);




        /* se l'attributo "data-attribute" è diverso da undefined...
        if ($(this).attr("data-attribute") != undefined ) {
            /* nascondi tutti i div
            $(".content-all>div").hide();
            /* mostra solo il div corrispondente al data attribute dell'elemento cliccato
            var selComp = $(this).attr('data-attribute');
            console.log(selComp);
            $(".content-all>div." + selComp).show();
            /* ...altrimenti
        } else {

            $(".content-all>div").show();
        }

         */

    });

    /*autosearch*/
    var searchText;
    $(".elsearch-input").keyup(function(){

        if ($(this).val().length > 2){
            /*facciamo delle cose*/
            searchText=$(".elsearch-input").val().toLowerCase();

            $(".element:not(.element-plus)").hide();




            //$(".element-plus").show();
            var resFound = 0;
            $(".element").each(function(){
                //console.log($(this).children(".eltitle").html().indexOf("Moto"));

                if ($(this).find(".eltitle").html().toLowerCase().indexOf(searchText) >= 0 || $(this).find(".paragraph").html().toLowerCase().indexOf(searchText) >= 0){
                    $(this).show();
                    resFound++;
                }

            });

            if (resFound == 0) {
                $(".zero-results").show();
            }

            if ($(".eltype-visible").length <= 1){
                $(".zero-results").hide(0);

            }

        } else {
            /*facciamo altre cose*/

            $(".element:not(.element-plus)").show();
            $(".content-all").scrollTop(0);
            $(".zero-results").hide(0);



        }




        if ($(this).val().length > 0){


            $(".deleter").show();
            $(".lens").hide();



        } else {


            $(".deleter").hide();
            $(".lens").show();


        }



        $(".deleter").click(cancellaTesto);


        function cancellaTesto(){
            $(".elsearch-input").val("").keyup();
            $(".deleter").hide();
            $(".lens").show();
            $(".content-all").scrollTop(0);

        }






    });

});

