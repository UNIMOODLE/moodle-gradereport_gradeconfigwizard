/* START: VARIABLES */

// Obtener los items seleccionados
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
    // Agregar evento change a cada checkbox de select-items
    for (let i = 0; i < selecteditemscheckboxes.length; i++) {
        selecteditemscheckboxes[i].addEventListener('change', function() {

            // START: Habilitar aggregation methods

            // Verificar si al menos 1 checkbox está marcado
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
                // if (aggregationmethodselect.value === "0") {
                weightsdefinitiondiv.classList.add("disabled-box");
                // }
            }

            // END: Habilitar aggregation methods

            // START: Añadir elementos a weights definition
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
                    '<input type="text"' +
                    ' data-enabled="' + weightenabled + '"' +
                    ' data-itemdisplayname="' + this.dataset.displayname + '"' +
                    ' data-itemidnumber="' + this.dataset.idnumber + '"' +
                    ' data-itemid="' + this.dataset.id + '"' +
                    ' data-paramtype="weight"' +
                    ' class="form-control" placeholder="">' +
                    '</td>';

                newrow.dataset.id = this.dataset.id;
            } else {
                let weightsdefinitionmatchingrow = weightsdefinitiontable.querySelector('[data-id="' + this.dataset.id + '"]');
                if (weightsdefinitionmatchingrow) {
                    weightsdefinitionmatchingrow.remove();
                }
            }

            // END: Añadir elementos a weights definition
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