/// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see &lt;http://www.gnu.org/licenses/&gt;.
// Project implemented by the &quot;Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU&quot;.
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/*
 * Display information about all the gradereport_gradeconfigwizard modules in the requested course. *
 * @package gradereport_gradeconfigwizard
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Joan Carbassa (IThinkUPC) &lt;joan.carbassa@ithinkupc.com&gt;
 * @author Yerai Rodríguez (IThinkUPC) &lt;yerai.rodriguez@ithinkupc.com&gt;
 * @author Marc Geremias (IThinkUPC) &lt;marc.geremias@ithinkupc.com&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

/* START: VARIABLES */

let selecteditemscheckboxes = document.querySelectorAll('.formulacreator-select-items input[type=checkbox]');

let aggregationmethoddiv = document.querySelector('.formulacreator-aggregation-method');
let weightsdefinitiondiv = document.querySelector('.formulacreator-weights-definition');
let weightsdefinitiontable = document.querySelector('.formulacreator-weights-definition table tbody');
let aggregationmethodselect = document.querySelector('.formulacreator-aggregation-method select');


let formulapreviewbox = document.querySelector('.formulacreator-formula');
let formulapreviewtextarea = document.querySelector('.formulacreator-formula textarea');
let previewformulabutton = document.getElementById('formulacreator-preview-formula');

/* END: VARIABLES */

/* START: FUNCTIONS */

const addEventToCheckbox = () => {
    // Enable event change to each checkbox of select-items
    for (let i = 0; i < selecteditemscheckboxes.length; i++) {
        selecteditemscheckboxes[i].addEventListener('change', function() {

            // START: Enable aggregation methods

            // Check if at least 1 checkbox is checked
            let alMenosUnoMarcado = false;
            for (let j = 0; j < selecteditemscheckboxes.length; j++) {
                if (selecteditemscheckboxes[j].checked) {
                    alMenosUnoMarcado = true;
                    break;
                }
            }
            // Habilitar o deshabilitar aggregation method selector según corresponda
            if (alMenosUnoMarcado) {
                aggregationmethoddiv.classList.remove("disabled-box");
                if (aggregationmethodselect.value !== "0") {
                    weightsdefinitiondiv.classList.remove("disabled-box");
                }
            } else {
                aggregationmethoddiv.classList.add("disabled-box");
                weightsdefinitiondiv.classList.add("disabled-box");
            }

            // END: Enable aggregation methods

            // START: Add elements a weights definition
            let weightenabled = '';
            if (this.checked) {
                if (aggregationmethodselect.value === 'weightedmeangrades') {
                    weightenabled = '1';
                } else {
                    weightenabled = '0';
                }

                let newrow = weightsdefinitiontable.insertRow(-1);
                newrow.innerHTML = '<th scope="row">' +
                    this.dataset.displayname +
                    '</th>' +
                    '<td>' +
                    '<input type="number"' +
                    ' min="0"' +
                    ' step="1"' +
                    ' data-enabled="' + weightenabled + '"' +
                    ' data-itemdisplayname="' + this.dataset.displayname + '"' +
                    ' data-itemidnumber="' + this.dataset.idnumber + '"' +
                    ' data-itemid="' + this.dataset.id + '"' +
                    ' data-paramtype="weight"' +
                    ' class="form-control" placeholder=""' +
                    ' value="1">' +
                    '</td>';

                newrow.dataset.id = this.dataset.id;
            } else {
                let weightsdefinitionmatchingrow = weightsdefinitiontable.querySelector('[data-id="' + this.dataset.id + '"]');
                if (weightsdefinitionmatchingrow) {
                    weightsdefinitionmatchingrow.remove();
                }
            }

            // END: Add elements a weights definition
        });
    }
};

const aggregationSelection = () => {
    aggregationmethodselect.addEventListener('change', function() {
        if (this.value === "0") {
            weightsdefinitiondiv.classList.add("disabled-box");
        } else {
            weightsdefinitiondiv.classList.remove("disabled-box");
        }
        let weightitems = weightsdefinitiondiv.querySelectorAll('[data-paramtype="weight"]');

        switch (this.value) {
            case 'weightedmeangrades':
                weightitems.forEach(function(weightitem) {
                    weightitem.dataset.enabled = 1;
                    weightitem.classList.remove("disabled-box");
                });
                break;
            case 'meangrades':
            case 'sum':
            case 'highest':
            case 'lowest':
                weightitems.forEach(function(weightitem) {
                    weightitem.dataset.enabled = 0;
                    weightitem.classList.add("disabled-box");
                });
                break;
        }

    });
};

const previewFormula = () => {

    previewformulabutton.addEventListener('click', function() {

        formulapreviewbox.classList.remove("disabled-box");

        let selectedformulaitems = weightsdefinitiontable.querySelectorAll('[data-paramtype="weight"]');
        let itemsformula = '';

        selectedformulaitems.forEach(function (selectedformulaitem) {
            let itemformula = "";
            itemformula = "  <ITEM>";

            itemformula += "<DISPLAYNAME>" + selectedformulaitem.dataset.itemdisplayname + "</DISPLAYNAME>";
            itemformula += "<IDNUMBER>" + selectedformulaitem.dataset.itemidnumber + "</IDNUMBER>";
            itemformula += "<GRADEITEMID>" + selectedformulaitem.dataset.itemid + "</GRADEITEMID>";

            //let weightvalue = '';
            if (selectedformulaitem.dataset.enabled === '1') {
                itemformula += "<WEIGHT>" + selectedformulaitem.value + "</WEIGHT>";
            }

            itemformula += "</ITEM>\n";

            itemsformula += itemformula;
        });

        let exampleformulas = {
            "meangrades": "<MEANGRADES>\n  " + itemsformula.trim() + "\n</MEANGRADES>",
            "weightedmeangrades": "<WEIGHTEDMEANGRADES>\n" + itemsformula.trim() + "\n</WEIGHTEDMEANGRADES>",
            "sum": "<SUM>\n" + itemsformula.trim() + "\n</SUM>",
            "highest": "<HIGHEST>\n" + itemsformula.trim() + "\n</HIGHEST>",
            "lowest": "<LOWEST>\n" + itemsformula.trim() + "\n</LOWEST>",
        };

        formulapreviewtextarea.value = exampleformulas[aggregationmethodselect.value];
    });
};
/* END: FUNCTIONS */


/* START: EVENT LISTENERS DECLARATION */

export const init = () => {
    addEventToCheckbox();
    aggregationSelection();
    previewFormula();
};

/* END: EVENT LISTENERS DECLARATION */
