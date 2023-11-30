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
 * @package gradeconfigwizard
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Joan Carbassa (IThinkUPC) &lt;joan.carbassa@ithinkupc.com&gt;
 * @author Yerai Rodríguez (IThinkUPC) &lt;yerai.rodriguez@ithinkupc.com&gt;
 * @author Marc Geremias (IThinkUPC) &lt;marc.geremias@ithinkupc.com&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

import $ from 'jquery';
import {get_string as getString} from 'core/str';

/* START: VARIABLES */

let weightedevaluationtable = document.getElementById('weightedevaluations-table');
let weightedevaluationtablebody = weightedevaluationtable.getElementsByTagName('tbody')[0];

let addcategorybtn = document.getElementById('weightedevaluations-category-create-btn');

let gradeitemsmodal = document.querySelector('#available-gradeitems-modal-id');
let gradeitemsmodalacceptbutton = gradeitemsmodal.querySelector('.confirm');

let saveandexitbutton = document.getElementsByClassName('saveandexit')[0];

/* END: VARIABLES */

/* START: TEMPLATES */
const categoryrowhtml = (randomid, categoryname, categorynamecontent) => `
<tr id="${randomid}" data-randomid="${randomid}" data-rowtype="category">
  <td class="category" rowspan="3">
    <input name="categories[${randomid}][name]" type="hidden" value="${categoryname}"/>
    ${categorynamecontent}
  </td>
</tr>
`;

const removeitemiconhtml = (randomid) => `
<i class="fa fa-fw fa-trash-o icon remove-item-icon" data-randomid="${randomid}"
title="Remove element" role="img" aria-label="Remove element"></i>
`;

const addsubcategorybuttonrowhtml = (randomid, str) => `
<tr data-randomid="${randomid}" data-rowtype="subcategory">
  <td class="subcategory add-btn" rowspan="2">
    <button type="button" class="add-subcategory-btn align-items-center block-add btn btn-secondary d-flex">
      <span class="pluscontainer icon-no-margin icon-size-3 d-flex p-2 mr-3">
          <i class="icon fa fa-plus fa-fw" aria-hidden="true"></i>
      </span>
      <span>${str}</span>
    </button>
  </td>
</tr>
`;

const subcategoryrowhtml = (randomid, subrandomid) => `
<tr id="'${subrandomid}" data-randomid="${randomid}" data-subrandomid="${subrandomid}" data-rowtype="subcategory">
  <td class="subcategory" rowspan="2">
    <input type="text" name="categories[${randomid}][subcategories][${subrandomid}][name]" value="">${removeitemiconhtml(randomid)}
  </td>
  <td class="weight-1">
    <input type="number" name="categories[${randomid}][subcategories][${subrandomid}][weight]" size="4" value="1">
  </td>
</tr>
`;

const addsubcategorygradeitembuttonrowhtml = (randomid, subrandomid, str) => `
<tr data-randomid="${randomid}" data-subrandomid="${subrandomid}" data-rowtype="subcategorygradeitem">
  <td class="subcategorygradeitem add-btn">
    <button type="button"
      class="add-subcategorygradeitem-btn align-items-center block-add btn btn-secondary d-flex"
      data-gradeitemaddtarget="subcategorygradeitem"
      data-toggle="modal"
      data-target="#available-gradeitems-modal-id">
      <span class="pluscontainer icon-no-margin icon-size-3 d-flex p-2 mr-3">
          <i class="icon fa fa-plus fa-fw" aria-hidden="true"></i>
      </span>
      <span>${str}</span>
    </button>
  </td>
  <td class="weight-2">
  </td>
</tr>
`;

const addsubcategorygradeitememptyrowhtml = (randomid, subrandomid) => `
<tr data-randomid="${randomid}" data-subrandomid="${subrandomid}" data-rowtype="subcategorygradeitem">
  <td class="subcategorygradeitem">
  </td>
  <td class="weight-2">
  </td>
</tr>
`;

const subcategorygradeitemrowhtml = (randomid, subrandomid, gradeitemid, gradeitemname) => `
<tr data-randomid="${randomid}" data-subrandomid="${subrandomid}"
data-gradeitemid="${gradeitemid}" data-rowtype="subcategorygradeitem">
  <td class="subcategorygradeitem">
  ${gradeitemname}${removeitemiconhtml(randomid)}
  <input name="categories[${randomid}][subcategories][${subrandomid}][items][${gradeitemid}][id]"
  value="${gradeitemid}" type="hidden">
  </td>
  <td class="weight-2">
    <input type="number" name="categories[${randomid}][subcategories][${subrandomid}][items][${gradeitemid}][weight]"
    size="4" value="1">
  </td>
</tr>
`;
/* END: TEMPLATES */

/* START: FUNCTIONS */

const generateUniqueId = () => {
    let randomid = null;
    while (randomid === null || document.getElementById(randomid) !== null) {
        // generate a 8 chars long random id
        randomid = Math.random().toString(36).substring(2, 10);
    }
    return randomid;
};

/* START: FUNCTIONS: GRADEITEMS MODAL */

const getGradeitemsmodalSelectedItems = () => {
    let gradeitems = gradeitemsmodal.querySelectorAll('input[type="checkbox"]:checked:not(.d-none)');
    return gradeitems;
};

const removeGradeitemsmodalItems = (gradeitems) => {
    gradeitems.forEach((gradeitem) => {
        gradeitem.closest('div').classList.add('d-none');
        gradeitem.checked = false;
    });
};

const addGradeitemsmodalItem = (gradeitemid) => {
    let gradeitem = gradeitemsmodal.querySelector('[data-id="' + gradeitemid + '"]');
    gradeitem.closest('div').classList.remove('d-none');
    gradeitem.checked = false;
};

/* END: FUNCTIONS: GRADEITEMS MODAL */

const recalculateRowspans = (randomid) => {
    // Category
    var categorytotalItemCount = weightedevaluationtablebody.querySelectorAll('tr[data-randomid="' + randomid + '"]').length;
    weightedevaluationtablebody.querySelectorAll('tr[data-randomid="' + randomid + '"][data-rowtype="category"] td')
        .forEach(datacell => {
            datacell.rowSpan = categorytotalItemCount;
        });

    // Subcategory
    weightedevaluationtablebody.querySelectorAll('tr[data-randomid="' + randomid + '"][data-rowtype="subcategory"]')
        .forEach(row => {
            let subrandomid = row.dataset.subrandomid;
            if (!subrandomid) {
                return;
            }
            let subcategorygradeitemsCount = weightedevaluationtablebody.querySelectorAll(
                'tr[data-rowtype="subcategorygradeitem"][data-subrandomid="' + subrandomid + '"]'
            ).length;
            var subcategorycategorytotalItemCount = 1 + subcategorygradeitemsCount;
            row.querySelectorAll('td').forEach(datacell => {
                datacell.rowSpan = subcategorycategorytotalItemCount;
            });
        });
};

const addCategory = async (categoryname) => {
    // In case new category is added, enable save and exit button
    if(saveandexitbutton.classList.contains('disabled-box')){
        saveandexitbutton.classList.remove('disabled-box');
    }

    let randomid = generateUniqueId();
    let categorynamecontent = categoryname + removeitemiconhtml(randomid);

    let newcategoryrowhtml = categoryrowhtml(randomid, categoryname, categorynamecontent);

    let newrowshtml = newcategoryrowhtml;

    await getString('additinerary1', 'gradereport_gradeconfigwizard')
    .then(function (str) {
        newrowshtml += addsubcategorybuttonrowhtml(randomid, str);
    });
    newrowshtml += addsubcategorygradeitememptyrowhtml(randomid, "");

    let weightedevaluationtablebodylastrow = weightedevaluationtablebody.querySelector('tr:last-child');
    if (weightedevaluationtablebodylastrow) {
        weightedevaluationtablebodylastrow.insertAdjacentHTML('afterend', newrowshtml);
    } else {
        weightedevaluationtablebody.innerHTML += newrowshtml;
    }

    let newcategoryrow = weightedevaluationtablebody.querySelector('[data-randomid="' + randomid + '"][data-rowtype="category"]');
    let newsubcategorybuttonrow = weightedevaluationtablebody.querySelector(
        '[data-randomid="' + randomid + '"][data-rowtype="subcategory"]'
    );

    let removeitemicon = newcategoryrow.querySelector('.remove-item-icon');
    removeitemicon.addEventListener('click', removeCategoryCallback);

    let addsubcategorybutton = newsubcategorybuttonrow.querySelector(
        'tr[data-randomid="' + randomid + '"] .subcategory .add-subcategory-btn'
    );
    addsubcategorybutton.addEventListener('click', addSubcategoryButtonCallback);
};

const removeCategory = (randomid) => {
    let subcategoryrowstodelete = weightedevaluationtablebody.querySelectorAll(
        'tr[data-rowtype="subcategory"][data-randomid="' + randomid + '"][data-subrandomid]'
    );
    subcategoryrowstodelete.forEach(subcategoryrow => {
        let subrandomid = subcategoryrow.dataset['subrandomid'];
        removeSubcategory(randomid, subrandomid);
    });

    let categoryrowstodelete = weightedevaluationtablebody.querySelectorAll('tr[data-randomid="' + randomid + '"]');
    categoryrowstodelete.forEach(row => row.remove());

    // If there are no categories left, disable save and exit button
    let categorycount = weightedevaluationtablebody.querySelectorAll('tr[data-rowtype="category"]').length;
    if (categorycount < 1) {
        saveandexitbutton.classList.add('disabled-box');
    }
};

const addSubcategory = async (randomid) => {
    let subrandomid = generateUniqueId();
    let subcategoryaddbtnrow = weightedevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="subcategory"] .add-subcategory-btn'
    ).closest('tr');

    await getString('additinerary2', 'gradereport_gradeconfigwizard')
    .then(function (str) {
        subcategoryaddbtnrow.insertAdjacentHTML('beforebegin', subcategoryrowhtml(randomid, subrandomid)
            + addsubcategorygradeitembuttonrowhtml(randomid, subrandomid, str));
    });

    let newsubcategoryrow = weightedevaluationtablebody.querySelector('' +
        'tr[data-randomid="' + randomid + '"][data-subrandomid="' + subrandomid + '"][data-rowtype="subcategory"]'
    );

    let removeitemicon = newsubcategoryrow.querySelector('.remove-item-icon');
    removeitemicon.addEventListener('click', removeSubcategoryCallback);

    recalculateRowspans(randomid);
};

const removeSubcategory = (randomid, subrandomid) => {
    let subcategorygradeitemrowtodelete = weightedevaluationtablebody.querySelectorAll('' +
        'tr[data-subrandomid="' + subrandomid + '"][data-gradeitemid][data-rowtype="subcategorygradeitem"]'
    );
    subcategorygradeitemrowtodelete.forEach(row => {
        removeSubcategoryGradeitem(randomid, subrandomid, row.dataset.gradeitemid);
    });
    let remainingrowtodelete = weightedevaluationtablebody.querySelectorAll(
        'tr[data-subrandomid="' + subrandomid + '"][data-rowtype="subcategorygradeitem"]'
    );
    remainingrowtodelete.forEach(row => {
        row.remove();
    });

    let subcategoryrowtodelete = weightedevaluationtablebody.querySelector(
        'tr[data-subrandomid="' + subrandomid + '"][data-rowtype="subcategory"]'
    );
    subcategoryrowtodelete.remove();

    recalculateRowspans(randomid);
};

const addSubcategoryGradeitems = (randomid, subrandomid, gradeitems) => {
    gradeitems.forEach(gradeitem => {
        addSubcategoryGradeitem(randomid, subrandomid, gradeitem);
    });
};

const addSubcategoryGradeitem = (randomid, subrandomid, gradeitem) => {
    let gradeitemid = gradeitem.dataset['id'];
    let gradeitemname = gradeitem.dataset['displayname'];
    let subcategorygradeitemaddbtnrow = weightedevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-subrandomid="' + subrandomid + '"][data-rowtype="subcategorygradeitem"]' +
        ' .subcategorygradeitem .add-subcategorygradeitem-btn'
    ).closest('tr');
    subcategorygradeitemaddbtnrow.insertAdjacentHTML('beforebegin',
        subcategorygradeitemrowhtml(randomid, subrandomid, gradeitemid, gradeitemname));

    let newsubcategorygradeitemrow = weightedevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"]' +
        '[data-subrandomid="' + subrandomid + '"][data-rowtype="subcategorygradeitem"][data-gradeitemid="' + gradeitemid + '"]'
    );

    let removeitemicon = newsubcategorygradeitemrow.querySelector('.remove-item-icon');
    removeitemicon.addEventListener('click', removeSubcategoryGradeItemCallback);

    recalculateRowspans(randomid);
};

const removeSubcategoryGradeitem = (randomid, subrandomid, gradeitemid) => {
    let subcategorygradeitemrow = weightedevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-subrandomid="' + subrandomid + '"]' +
        '[data-gradeitemid="' + gradeitemid + '"][data-rowtype="subcategorygradeitem"]'
    );
    subcategorygradeitemrow.remove();

    addGradeitemsmodalItem(gradeitemid);
    recalculateRowspans(randomid);
};

/* END: FUNCTIONS */

/* START: CALLBACKS */

const addCategoryButtonClickCallback = () => {
    let categorynameinput = document.getElementById('weightedevaluations-category-create-name');
    categorynameinput.value = categorynameinput.value.trim();
    let categoryname = categorynameinput.value;
    if (categoryname === '') {
        return;
    }
    addCategory(categoryname);
    categorynameinput.value = '';
};

const availableGradeItemsModalShowCallback = (event) => {
    //@TODO check if all works properly
    let button = event.relatedTarget;
    let callerrandomid = button.closest('tr').dataset['randomid'];
    let gradeitemaddtarget = button.dataset['gradeitemaddtarget'];
    let gradeitemid = button.closest('td').dataset['gradeitemid'];
    let callersubrandomid = button.closest('tr').dataset.subrandomid;

    gradeitemsmodal.dataset['gradeitemaddtarget'] = gradeitemaddtarget;
    gradeitemsmodal.dataset['callerrandomid'] = callerrandomid;
    gradeitemsmodal.dataset['callersubrandomid'] = callersubrandomid;
    gradeitemsmodal.dataset['gradeitemid'] = gradeitemid;
};

const availableGradeItemsModalConfirmCallback = () => {
    let gradeitemaddtarget = gradeitemsmodal.dataset['gradeitemaddtarget'];

    let randomid = gradeitemsmodal.dataset['callerrandomid'];
    let subrandomid = gradeitemsmodal.dataset['callersubrandomid'];
    let gradeitems = getGradeitemsmodalSelectedItems();

    switch (gradeitemaddtarget) {
        case 'subcategorygradeitem':
            addSubcategoryGradeitems(randomid, subrandomid, gradeitems);
            removeGradeitemsmodalItems(gradeitems);
            $(gradeitemsmodal).modal('hide');
            break;
    }
};

const removeCategoryCallback = (event) => {
    let randomid = event.target.dataset.randomid;
    removeCategory(randomid);
};

const addSubcategoryButtonCallback = (event) => {
    let randomid = event.target.closest('tr').dataset['randomid'];
    addSubcategory(randomid);
};

const removeSubcategoryCallback = (event) => {
    let targetrow = event.target.closest('tr');
    let randomid = targetrow.dataset.randomid;
    let subrandomid = targetrow.dataset.subrandomid;
    removeSubcategory(randomid, subrandomid);
};

const removeSubcategoryGradeItemCallback = (event) => {
    let targetrow = event.target.closest('tr');
    let randomid = targetrow.dataset.randomid;
    let subrandomid = targetrow.dataset.subrandomid;
    let gradeitemid = targetrow.dataset.gradeitemid;
    removeSubcategoryGradeitem(randomid, subrandomid, gradeitemid);
};

/* END: CALLBACKS */

/* START: EVENT LISTENERS DECLARATION */

export const init = () => {
    addcategorybtn.addEventListener('click', addCategoryButtonClickCallback);
    $(gradeitemsmodal).on('show.bs.modal', availableGradeItemsModalShowCallback);
    gradeitemsmodalacceptbutton.addEventListener('click', availableGradeItemsModalConfirmCallback);
};

/* END: EVENT LISTENERS DECLARATION */
