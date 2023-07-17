import $ from 'jquery';

/* START: VARIABLES */

let multipleevaluationtable = document.getElementById('multipleevaluation-table');
let multipleevaluationtablebody = multipleevaluationtable.getElementsByTagName('tbody')[0];

let addcategorybtn = document.getElementById('multipleevaluations-category-create-btn');

let gradeitemsmodal = document.querySelector('#available-gradeitems-modal-id');
let gradeitemsmodalacceptbutton = gradeitemsmodal.querySelector('.confirm');

/* END: VARIABLES */

/* START: TEMPLATES */

const removeitemiconhtml = (randomid) => `
<i class="fa fa-fw fa-trash-o icon remove-item-icon" data-randomid="${randomid}" 
title="Remove element" role="img" aria-label="Remove element"></i>
`;

const categoryNameInput = (randomid, categoryname) => `
${categoryname}
<input name="categories[${randomid}][name]" type="hidden" value="${categoryname}"/>
`;

const newcategoryextracellshtml = (randomid) => `
<td class="weight-1" rowspan="2">
  <input type="number" name="categories[${randomid}][weight]" size="2" value="1">
</td>
<td class="min-grade-1" rowspan="2">
  <div class="container">
    <div class="row">
      <input type="checkbox" class="mr-2" id="myCheckbox" disabled>
      <input type="number" name="categories[${randomid}][recitem][recitemgrade]" size="2" disabled>
    </div>
  </td>
  </div>
<td class="resit-1 add-btn" rowspan="2">
  <button type="button"
    class="add-resit-items-btn btn btn-secondary"
    data-toggle="modal"
    data-target="#available-gradeitems-modal-id"
    data-gradeitemaddtarget="categoryresititem"
    data-addgradeitemdepth="1" disabled>
      Añadir elemento
  </button>
</td>
`;

const addcategoryelementsbuttonrowhtml = (randomid) => `
<tr data-randomid="${randomid}" data-rowtype="categorygradeitemaddbtn" data-depthlevel="2">
  <td class="item">
    <button type="button"
      data-toggle="modal"
      data-target="#available-gradeitems-modal-id"
      data-gradeitemaddtarget="gradeitem"
      data-addgradeitemdepth="2"
      class="add-category-items-btn btn btn-secondary">
        Añadir elementos
    </button>
  </td>
  <td class="weight-2"></td>
  <td class="min-grade-2"></td>
  <td class="resit-2"></td>
</tr>
`;

const categorygradeitemrowhtml = (randomid, gradeitemid, gradeitemname) => `
<tr data-randomid="${randomid}" data-rowtype="categorygradeitem" data-gradeitemid="${gradeitemid}" data-depthlevel="2">
  <td class="item"
    data-gradeitemid="${gradeitemid}"
    data-gradeitemname="${gradeitemname}"
    >
      ${gradeitemname}
      <input name="categories[${randomid}][items][${gradeitemid}][id]" type="hidden" value="${gradeitemid}" />
      <i class="fa fa-fw fa-trash-o icon remove-item-icon" data-randomid="${randomid}" data-gradeitemid="${gradeitemid}" 
      title="Remove element" role="img" aria-label="Remove element"></i>
  </td>
  <td class="weight-2">
    <input type="number" name="categories[${randomid}][items][${gradeitemid}][weight]" size="2" value="1" />
  </td>
  <td class="min-grade-2">
    <div class="container">
      <div class="row">
        <input type="checkbox" class="mr-2">
        <input name="categories[${randomid}][items][${gradeitemid}][recitem][recitemgrade]" type="number" size="2" disabled>
      </div>
    </div>
  </td>
  <td class="resit-2" data-gradeitemid="${gradeitemid}">
    <button type="button"
      class="add-grade-item-resit-items-btn btn btn-secondary"
      data-gradeitemaddtarget="gradeitemresititem"
      data-toggle="modal"
      data-target="#available-gradeitems-modal-id"
      disabled>
        Añadir elemento
    </button>
  </td>
</tr>
`;

const categoryresitgradeitemcellhtml = (randomid, gradeitemid, gradeitemname, rowspan) => `
<td class="resit-1" rowspan="${rowspan}" data-gradeitemid="${gradeitemid}" data-gradeitemname="${gradeitemname}">
  ${gradeitemname}
  <input name="categories[${randomid}][recitem][recitemid]" type="hidden" value="${gradeitemid}"/>
  <i class="fa fa-fw fa-trash-o icon remove-item-icon" title="Remove element" role="img" aria-label="Remove element"></i>
</td>
`;

const categorygradeitemresitgradeitemcellhtml = (randomid, gradeitemid, gradeitemname, parentgradeitemid) => `
<td class="resit-2" data-gradeitemid="${gradeitemid}" data-gradeitemname="${gradeitemname}">
  ${gradeitemname}
  <input name="categories[${randomid}][items][${parentgradeitemid}][recitem][recitemid]" type="hidden" value="${gradeitemid}" />
  <i class="fa fa-fw fa-trash-o icon remove-item-icon" title="Remove element" role="img" aria-label="Remove element"></i>
</td>
`;

const addcategoryresitbuttoncellenabledhtml = (randomid) => `
<td class="resit-1 add-btn" rowspan="2" id="categoryresitbutton-${randomid}">
  <button type="button"
    class="add-resit-items-btn btn btn-secondary"
    data-toggle="modal"
    data-target="#available-gradeitems-modal-id"
    data-gradeitemaddtarget="categoryresititem"
    data-addgradeitemdepth="1">
      Añadir elemento
  </button>
</td>
`;

const addcategorygradeitemresitgradeitembuttoncellenabledhtml = (gradeitemid) => `
<td class="resit-2 add-btn" data-gradeitemid="${gradeitemid}">
  <button type="button"
    class="add-grade-item-resit-items-btn btn btn-secondary"
    data-gradeitemaddtarget="gradeitemresititem"
    data-toggle="modal"
    data-target="#available-gradeitems-modal-id">
      Añadir elemento
  </button>
</td>
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

const recalculateRowspans = (randomid) => {
    let categoryCount = 1;
    let categoryGradeItemAddBtnCount = multipleevaluationtablebody.querySelectorAll(
        '[data-randomid="' + randomid + '"][data-rowtype="categorygradeitemaddbtn"]'
    ).length;
    let categoryGradeItemCount = multipleevaluationtablebody.querySelectorAll(
        '[data-randomid="' + randomid + '"][data-rowtype="categorygradeitem"]'
    ).length;
    var totalItemCount = categoryGradeItemAddBtnCount + categoryGradeItemCount + categoryCount;
    multipleevaluationtablebody.querySelectorAll(
        '[data-randomid="' + randomid + '"][data-rowtype="category"] td'
    ).forEach(datacell => {
        datacell.rowSpan = totalItemCount;
    });
};

const addCategory = (categoryname) => {
    let randomid = generateUniqueId();

    let newcategoryrow = document.createElement('tr');
    newcategoryrow.id = randomid;
    newcategoryrow.dataset.randomid = randomid;
    newcategoryrow.dataset.depthlevel = 1;
    newcategoryrow.dataset.rowtype = "category";

    let newcategorycell = document.createElement('td');
    newcategorycell.classList.add('category');
    newcategorycell.rowSpan = 2;
    newcategorycell.innerHTML = categoryNameInput(randomid, categoryname) + removeitemiconhtml(randomid); //not needed

    newcategoryrow.appendChild(newcategorycell);
    newcategoryrow.innerHTML += newcategoryextracellshtml(randomid);

    multipleevaluationtablebody.appendChild(newcategoryrow);

    newcategoryrow.insertAdjacentHTML('afterend', addcategoryelementsbuttonrowhtml(randomid));

    let removeitemicon = newcategoryrow.querySelector('.remove-item-icon');
    removeitemicon.addEventListener('click', removeCategoryCallback);

    let categorymingradecheckbox = newcategoryrow.querySelector('.min-grade-1 input[type="checkbox"]');
    categorymingradecheckbox.addEventListener('change', categoryMinGradeCheckboxChangeCallback);

    let categorymingradetext = newcategoryrow.querySelector('.min-grade-1 input[type="number"]');
    categorymingradetext.addEventListener('input', categoryMinGradeTextChangeCallback);
};

const removeCategory = (randomid) => {
    removeCategoryResititem(randomid);

    let catgorygradeitemrowstodelete = multipleevaluationtablebody.querySelectorAll(
        'tr[data-rowtype="categorygradeitem"][data-randomid="' + randomid + '"]'
    );
    catgorygradeitemrowstodelete.forEach(catgorygradeitemrow => {
        let randomid = catgorygradeitemrow.dataset['randomid'];
        let gradeitemid = catgorygradeitemrow.dataset['gradeitemid'];
        removeCategoryGradeitem(randomid, gradeitemid);
    });

    let categoryrowstodelete = multipleevaluationtablebody.querySelectorAll('tr[data-randomid="' + randomid + '"]');
    categoryrowstodelete.forEach(row => row.remove());
};

const addCategoryGradeitems = (randomid, gradeitems) => {
    gradeitems.forEach(gradeitem => {
        addCategoryGradeitem(randomid, gradeitem);
    });
};

const addCategoryGradeitem = (randomid, gradeitem) => {
    let gradeitemid = gradeitem.dataset['id'];
    let gradeitemname = gradeitem.dataset['displayname'];
    let categorygradeitemaddbtn = multipleevaluationtablebody.querySelectorAll(
        '[data-randomid="' + randomid + '"][data-rowtype="categorygradeitemaddbtn"]'
    );
    categorygradeitemaddbtn[0].insertAdjacentHTML('beforebegin', categorygradeitemrowhtml(randomid, gradeitemid, gradeitemname));

    let newcategorygradeitemrow = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="categorygradeitem"][data-gradeitemid="' + gradeitemid + '"]'
    );

    let removeitemicon = newcategorygradeitemrow.querySelector('.remove-item-icon');
    removeitemicon.addEventListener('click', removeCategoryGradeItemCallback);

    let categorygradeitemmingradecheckbox = newcategorygradeitemrow.querySelector('.min-grade-2 input[type="checkbox"]');
    categorygradeitemmingradecheckbox.addEventListener('change', categoryGradeitemMinGradeCheckboxChangeCallback);

    let categorygradeitemmingradetext = newcategorygradeitemrow.querySelector('.min-grade-2 input[type="number"]');
    categorygradeitemmingradetext.addEventListener('input', categoryGradeitemMinGradeTextChangeCallback);

    let categorymingradecheckbox = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="category"] .min-grade-1 input[type="checkbox"]'
    );
    categorymingradecheckbox.disabled = false;

    recalculateRowspans(randomid);
};

const removeCategoryGradeitem = (randomid, gradeitemid) => {
    let rowtodelete = multipleevaluationtablebody.querySelectorAll(
        'tr[data-randomid="' + randomid + '"][data-rowtype="categorygradeitem"][data-gradeitemid="' + gradeitemid + '"]'
    );
    rowtodelete.forEach(row => {
        row.querySelectorAll('[data-gradeitemid]').forEach(row => addGradeitemsmodalItem(row.dataset['gradeitemid']));
        row.remove();
    });

    let categorygradeitems = multipleevaluationtablebody.querySelectorAll(
        'tr[data-randomid="' + randomid + '"][data-rowtype="categorygradeitem"]'
    );
    let categorymingradecheckbox = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="category"] .min-grade-1 input[type="checkbox"]'
    );
    if (categorygradeitems.length > 0) {
        categorymingradecheckbox.disabled = false;
    } else {
        categorymingradecheckbox.disabled = true;
    }

    addGradeitemsmodalItem(gradeitemid);
    recalculateRowspans(randomid);
};

const addCategoryresititem = (randomid, gradeitem) => {
    let gradeitemid = gradeitem.dataset['id'];
    let gradeitemname = gradeitem.dataset['displayname'];
    let categoryresitcell = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="category"] td.resit-1'
    );
    let rowspan = categoryresitcell.getAttribute('rowspan');

    let categoryresitgradeitemaddbtn = multipleevaluationtablebody.querySelector(
        '[data-randomid="' + randomid + '"] td.resit-1 .add-resit-items-btn'
    );
    let categoryresitgradeitemcell = categoryresitgradeitemaddbtn.closest('td');
    categoryresitgradeitemcell.insertAdjacentHTML('beforebegin',
        categoryresitgradeitemcellhtml(randomid, gradeitemid, gradeitemname, rowspan))
    ;
    categoryresitgradeitemcell.remove();

    let newcategoryresitgradeitem = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="category"] td.resit-1[data-gradeitemid="' + gradeitemid + '"]'
    );
    newcategoryresitgradeitem.querySelector('.remove-item-icon').addEventListener('click', removeCategoryResitGradeitemCallback);
};

const removeCategoryResititem = (randomid, gradeitemid = null) => {
    let categoryresitgradeitem = null;
    if (gradeitemid === null) {
        categoryresitgradeitem = multipleevaluationtablebody.querySelector('tr[data-randomid="' + randomid + '"] td.resit-1');
        gradeitemid = categoryresitgradeitem.dataset['gradeitemid'];
    } else {
        categoryresitgradeitem = multipleevaluationtablebody.querySelector(
            'tr[data-randomid="' + randomid + '"] td.resit-1[data-gradeitemid="' + gradeitemid + '"]'
        );
    }
    let categoryresitgradeitemcell = categoryresitgradeitem.closest('td');
    categoryresitgradeitemcell.insertAdjacentHTML('afterend', addcategoryresitbuttoncellenabledhtml(randomid));
    categoryresitgradeitemcell.remove();

    if (gradeitemid !== undefined) {
        addGradeitemsmodalItem(gradeitemid);
    }
    recalculateRowspans(randomid);
};

const disableAddCategoryResititmeBtn = (randomid) => {
    let addcategoryresititmebtn = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"] td.resit-1.add-btn button'
    );
    if (addcategoryresititmebtn) {
        addcategoryresititmebtn.disabled = true;
    }
};

const addCategoryGradeitemResititem = (randomid, parentgradeitem, gradeitem) => {
    let gradeitemid = gradeitem.dataset['id'];
    let gradeitemname = gradeitem.dataset['displayname'];

    let categorygradeitemresitgradeitemaddbtn = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"] td[data-gradeitemid="' +
        parentgradeitem + '"] ~ td.resit-2 .add-grade-item-resit-items-btn'
    );
    let categorygradeitemresitgradeitemcell = categorygradeitemresitgradeitemaddbtn.closest('td');
    categorygradeitemresitgradeitemcell.insertAdjacentHTML('beforebegin',
        categorygradeitemresitgradeitemcellhtml(randomid, gradeitemid, gradeitemname, parentgradeitem));
    categorygradeitemresitgradeitemcell.remove();

    let newcategorygradeitemresitgradeitem = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"][data-rowtype="categorygradeitem"] td.resit-2[data-gradeitemid="' + gradeitemid + '"]'
    );
    newcategorygradeitemresitgradeitem.querySelector('.remove-item-icon')
        .addEventListener('click', removeCategoryGradeitemResitGradeitemCallback);
};

const removeCategoryGradeitemResititem = (randomid, parentgradeitemid, gradeitemid) => {
    let categoryresitgradeitemcell = multipleevaluationtablebody.querySelector(
        'td.resit-2[data-gradeitemid="' + gradeitemid + '"]'
    );
    categoryresitgradeitemcell.insertAdjacentHTML('afterend',
        addcategorygradeitemresitgradeitembuttoncellenabledhtml(parentgradeitemid));
    categoryresitgradeitemcell.remove();

    //TODO: esto está hecho porque hasta que no se define un item de recuperación,
    // el botón para añadir uno tiene el id del itemgrade al que irá asociado
    if (parentgradeitemid != gradeitemid) {
        addGradeitemsmodalItem(gradeitemid);
    }
    recalculateRowspans(randomid);
};

const disableAddCategoryGradeitemResititemBtn = (randomid) => {
    let addcategorygradeitemresititembtn = multipleevaluationtablebody.querySelector(
        'tr[data-randomid="' + randomid + '"] td.resit-2.add-btn button.add-grade-item-resit-items-btn'
    );
    if (addcategorygradeitemresititembtn) {
        addcategorygradeitemresititembtn.disabled = true;
    }
};

/* END: FUNCTIONS */

/* START: CALLBACKS */

const addCategoryButtonClickCallback = () => {
    let categorynameinput = document.getElementById('multipleevaluations-category-create-name');
    categorynameinput.value = categorynameinput.value.trim();
    let categoryname = categorynameinput.value;
    if (categoryname === '') {
        return;
    }
    addCategory(categoryname);
    categorynameinput.value = '';
};

const removeCategoryCallback = (event) => {
    let randomid = event.target.dataset.randomid;
    removeCategory(randomid);
};

const removeCategoryGradeItemCallback = (event) => {
    let randomid = event.target.dataset.randomid;
    let gradeitemid = event.target.dataset.gradeitemid;
    removeCategoryGradeitem(randomid, gradeitemid);
};

const removeCategoryResitGradeitemCallback = (event) => {
    let parenttd = event.target.closest('td');
    let parenttr = parenttd.closest('tr');
    let randomid = parenttr.dataset['randomid'];
    let gradeitemid = parenttd.dataset['gradeitemid'];
    removeCategoryResititem(randomid, gradeitemid);
};

const removeCategoryGradeitemResitGradeitemCallback = (event) => {
    let parenttd = event.target.closest('td');
    let parenttr = parenttd.closest('tr');
    let randomid = parenttr.dataset['randomid'];
    let gradeitemid = parenttd.dataset['gradeitemid'];
    let parentgradeitemid = parenttr.querySelector('td.item').dataset['gradeitemid'];
    removeCategoryGradeitemResititem(randomid, parentgradeitemid, gradeitemid);
};

const availableGradeItemsModalShowCallback = (event) => {
    let button = event.relatedTarget;
    let callerrandomid = button.closest('tr').dataset['randomid'];
    let gradeitemaddtarget = button.dataset['gradeitemaddtarget'];
    let addgradeitemdepth = button.dataset['addgradeitemdepth'];
    let gradeitemid = button.closest('td').dataset['gradeitemid'];

    gradeitemsmodal.dataset['gradeitemaddtarget'] = gradeitemaddtarget;
    gradeitemsmodal.dataset['addgradeitemdepth'] = addgradeitemdepth;
    gradeitemsmodal.dataset['callerrandomid'] = callerrandomid;
    gradeitemsmodal.dataset['gradeitemid'] = gradeitemid;
};

const availableGradeItemsModalConfirmCallback = () => {
    let gradeitemaddtarget = gradeitemsmodal.dataset['gradeitemaddtarget'];
    let gradeitemid = gradeitemsmodal.dataset['gradeitemid'];

    let randomid = gradeitemsmodal.dataset['callerrandomid'];
    let gradeitems = getGradeitemsmodalSelectedItems();

    switch (gradeitemaddtarget) {
        case 'gradeitem':
            addCategoryGradeitems(randomid, gradeitems);
            removeGradeitemsmodalItems(gradeitems);
            $(gradeitemsmodal).modal('hide');
            break;
        case 'categoryresititem':
            if (gradeitems.length > 1) {
                alert('Selecciona solo 1 gradeitem');
                break;
            }
            addCategoryresititem(randomid, gradeitems[0]);
            removeGradeitemsmodalItems(gradeitems);
            $(gradeitemsmodal).modal('hide');
            break;
        case 'gradeitemresititem':
            if (gradeitems.length > 1) {
                alert('Selecciona solo 1 gradeitem');
                break;
            }
            addCategoryGradeitemResititem(randomid, gradeitemid, gradeitems[0]);
            removeGradeitemsmodalItems(gradeitems);
            $(gradeitemsmodal).modal('hide');
            break;
    }
};

const categoryMinGradeCheckboxChangeCallback = (event) => {
    let categorymingradecheckbox = event.target;
    let categorymingradecheckboxrow = categorymingradecheckbox.closest('tr');
    let randomid = categorymingradecheckboxrow.dataset['randomid'];

    let categoryresititemid = categorymingradecheckboxrow.querySelector('.resit-1').dataset['gradeitemid'];
    if (categoryresititemid) {
        removeCategoryResititem(randomid, categoryresititemid);
        disableAddCategoryResititmeBtn(randomid);
    }

    let categorymingradeinput = categorymingradecheckboxrow.querySelector('.min-grade-1 input[type="number"]');
    if (categorymingradecheckbox.checked) {
        categorymingradeinput.disabled = false;
    } else {
        categorymingradeinput.disabled = true;
        categorymingradeinput.value = '';
    }
};

const categoryGradeitemMinGradeCheckboxChangeCallback = (event) => {
    let categorymingradecheckbox = event.target;
    let categorygradeitemchangedrow = categorymingradecheckbox.closest('tr');
    let randomid = categorygradeitemchangedrow.dataset['randomid'];

    let parentgradeitemid = categorygradeitemchangedrow.querySelector('td.item').dataset['gradeitemid'];
    let resitgradeitemid = categorygradeitemchangedrow.querySelector('td.resit-2').dataset['gradeitemid'];
    if (resitgradeitemid !== null && parentgradeitemid !== null) {
        removeCategoryGradeitemResititem(randomid, parentgradeitemid, resitgradeitemid);
        disableAddCategoryGradeitemResititemBtn(randomid);
    }

    let categorymingradeinput = categorygradeitemchangedrow.querySelector('.min-grade-2 input[type="number"]');

    if (categorymingradecheckbox.checked) {
        categorymingradeinput.disabled = false;
    } else {
        categorymingradeinput.disabled = true;
        categorymingradeinput.value = '';
    }
};

const categoryMinGradeTextChangeCallback = (event) => {
    let mingradetextfield = event.target;
    let mingradetextfieldrow = mingradetextfield.closest('tr');

    let addCategoryGradeitemResititemBtn = mingradetextfieldrow.querySelector('.resit-1 button');

    if (mingradetextfield.value.trim() === '') {
        addCategoryGradeitemResititemBtn.disabled = true;
    } else {
        addCategoryGradeitemResititemBtn.disabled = false;
    }
};

const categoryGradeitemMinGradeTextChangeCallback = (event) => {
    let mingradetextfield = event.target;
    let mingradetextfieldrow = mingradetextfield.closest('tr');

    let addCategoryGradeitemResititemBtn = mingradetextfieldrow.querySelector('.resit-2 button');

    if (mingradetextfield.value.trim() === '') {
        addCategoryGradeitemResititemBtn.disabled = true;
    } else {
        addCategoryGradeitemResititemBtn.disabled = false;
    }
};

/* END: CALLBACKS */

/* START: EVENT LISTENERS DECLARATION */

export const init = () => {
    addcategorybtn.addEventListener('click', addCategoryButtonClickCallback);
    $(gradeitemsmodal).on('show.bs.modal', availableGradeItemsModalShowCallback);
    gradeitemsmodalacceptbutton.addEventListener('click', availableGradeItemsModalConfirmCallback);
};

/* END: EVENT LISTENERS DECLARATION */