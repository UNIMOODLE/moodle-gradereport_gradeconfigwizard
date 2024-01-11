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


/* START: VARIABLES */

let gradeconfigwizarddashboardtable = document.getElementById('gradeconfigwizard-dashboard-table');
let gradeconfigwizarddashboardtablebody = gradeconfigwizarddashboardtable.querySelector('tbody');

let courseidglobal;
let actionurlglobal;
let urlformulacreatorglobal;
let gradeitemidglobal;
let wwwrootglobal;

/* END: VARIABLES */


/* START: TEMPLATES */

const removeitemiconhtml = (randomid) => `<i class="fa fa-fw fa-trash-o icon remove-item-icon" 
data-randomid="${randomid}" title="Remove element" role="img" aria-label="Remove element"></i>`;

const gradeitemrowhtml = (randomid, gradecategoryid, gradeitemid, depth, parentrandomid, alreadycreatedcategory) => `
    <tr data-gradecategoryid="${gradecategoryid}" data-gradeitemid="${gradeitemid}"
     data-itemtype="manual" data-randomid="${randomid}" id="${randomid}" data-categorydepth="${depth}">
        <td data-id="${gradeitemid}" data-displayname="">
            ${getDepthPadding(depth)}
            <i class="fa fa-square-o fa-fw icon itemicon" title="Category"
             role="img" aria-label="Category"></i>
            <input class="subcategoryname" name="subcategoryname[${randomid}]" 
            placeholder="Grade item name" value="" required>${removeitemiconhtml(randomid)}
            ${alreadycreatedcategory} 
        </td>
        <td>
            <span class="p-2"></span>
            <input type="text" name="weight" id="" value="1" size="4" disabled=""
             class="gradeitem-weight form-control d-inline-block">
        </td>
        <td>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle disabled" 
                type="button" id="dropdownMenuButton-${gradecategoryid}-${gradeitemid}" data-toggle="dropdown" 
                aria-haspopup="true" aria-expanded="false" >
                    Edit
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton-${gradecategoryid}-${gradeitemid}">
                    ${newgradeitemeditactioneditcalculationhtml}
                    ${newgradeitemeditactiondisablehtml}
                </div>
            </div>
        </td>
    </tr>
    `;

const gradeitemcategoryrowhtml = (randomid, gradecategoryid, gradeitemid, depth, parentrelativepath) => `
    <tr data-gradecategoryid="${gradecategoryid}" data-gradeitemid="${gradeitemid}" 
    data-itemtype="category" data-randomid="${randomid}" id="${randomid}" 
    data-categorydepth="${depth}" data-parentrelativepath="${parentrelativepath}">
        <td data-id="${gradeitemid}" data-displayname="">
            ${getDepthPadding(depth)}
            <i class="fa fa-folder fa-fw icon itemicon" title="Category" role="img" aria-label="Category"></i>
            <input class="subcategoryname" name="randomnames_dictionary[${randomid}]"
             placeholder="Category name" value="" required>${removeitemiconhtml(randomid)}
            <input type="hidden" name="relativepaths[${randomid}]" value="${parentrelativepath}">
        </td>
        <td>
            <span class="p-2"></span>
            <input type="text" name="weight" id="" value="1" 
            size="4" disabled="" class="gradeitem-weight form-control d-inline-block">
        </td>
        <td>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" 
                type="button" id="dropdownMenuButton-${gradecategoryid}-${gradeitemid}" 
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Edit
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton-${gradecategoryid}-${gradeitemid}">
                    ${newgradeitemeditactionaddgradeitemcategoryhtml}
                    ${newgradeitemeditactionaddgradeitemhtml}
                    ${newgradeitemeditactiondisablehtml}
                </div>
            </div>
        </td>
    </tr>
    `;

const gradeitemcategorytotalrowhtml = (randomid, gradecategoryid, gradeitemid, depth) => `
    <tr data-gradecategoryid="${gradecategoryid}" data-gradeitemid="${gradeitemid}" data-itemtype="category" 
    data-randomid="${randomid}" data-istotal="1" id="${randomid}" data-categorydepth="${depth}">
        <td data-id="${gradeitemid}" data-displayname="">
            ${getDepthPadding(depth)}
            <i class="fa fa-list fa-fw icon itemicon" title="Category total" role="img" aria-label="Category total"></i>
            <input class="subcategoryname" name="subcategoryname" value="" disabled="true">
        </td>
        <td>
        </td>
        <td>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle disabled" type="button" 
                id="dropdownMenuButton-${gradecategoryid}-${gradeitemid}" data-toggle="dropdown" 
                aria-haspopup="true" aria-expanded="false">
                    Edit
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton-${gradecategoryid}-${gradeitemid}">
                    ${newgradeitemeditactioneditcalculationhtml}
                </div>
            </div>
        </td>
    </tr>
    `;

const newgradeitemeditactionaddgradeitemcategoryhtml = `
    <a class="add-gradeitemcategory-btn dropdown-item" href="#">
        <i class="fa fa-plus fa-fw icon itemicon" title="Add category" role="img" aria-label="Add category"></i>
        Add category
    </a>
    `;

const newgradeitemeditactionaddgradeitemhtml = `
    <a class="add-gradeitem-btn dropdown-item" href="#">
        <i class="fa fa-plus fa-fw icon itemicon" title="Add gradeitem" role="img" aria-label="Add gradeitem"></i>
        Add grade item
    </a>
    `;

const newgradeitemeditactioneditcalculationhtml = `
    <a class="dropdown-item" href="${urlformulacreatorglobal}id=${courseidglobal}&gradeitemid=${gradeitemidglobal}">
    <i class="fa fa-calculator fa-fw icon itemicon" title="" role="img" aria-label=""></i>
    Edit calculation
    </a>
    `;


const newgradeitemeditactiondisablehtml = `
    <a class="gradeitem-disable-btn dropdown-item disabled" href="#">
        <i class="fa fa-minus fa-fw icon itemicon" title="" role="img" aria-label=""></i>
        Disable
    </a>
    `;


/* END: TEMPLATES */

/* START: FUNCTIONS */

let defaultPathSeparator = '/';


const generateUniqueId = () => {
    let randomid = null;
    while (randomid === null || document.getElementById(randomid) !== null) {
        // generate a 8 chars long random id
        randomid = Math.random().toString(36).substring(2, 10);
    }
    return randomid;
};

const getDepthPadding = (depth) => {
    depth = parseInt(depth);
    let response = '';
    for (let i = 0; i < depth; i++) {
        response += '<span class="p-2"></span>';
    }
    return response;
};

const addGradeitemAfterRow = (row) => {
    disableDragging();
    let gradecategoryid = row.dataset.gradecategoryid;
    let gradeitemid = row.dataset.gradeitemid;
    let depth = row.dataset.categorydepth;

    let parentrandomid = row.dataset.randomid;

    let newrandomid = generateUniqueId();
    let newdepth = parseInt(depth) + 1;

    let alreadycreatedcategory;

    // The "/" is used to identify that the parent is a category already created and then the value
    // is the randomid of the category, otherwise if the parent is a category that isn't created yet
    // the value is the randomid of the parent category.
    if (parentrandomid == "") {
        alreadycreatedcategory = `<input type="hidden" name="gradeitemparent[${newrandomid}]" value="/${gradecategoryid}">`;
    } else {
        alreadycreatedcategory = `<input type="hidden" name="gradeitemparent[${newrandomid}]" value="${parentrandomid}">`;
    }

    let newgradeitemrowhtml =
        gradeitemrowhtml(newrandomid, gradecategoryid, gradeitemid, newdepth, parentrandomid, alreadycreatedcategory);
    row.insertAdjacentHTML('afterend', newgradeitemrowhtml);
    let newgradeitemrow = document.getElementById(newrandomid);
    newgradeitemrow.querySelector('.remove-item-icon').addEventListener('click', removeGradeitemButtonClickCallback);
    newgradeitemrow.querySelector('.gradeitem-disable-btn').addEventListener('click', disableGradeitemButtonClickCallback);

    return newgradeitemrow;
};

const removeGradeitemRow = (gradeitemrow) => {
    gradeitemrow.remove();
    enableDragging();
};

const disableGradeitem = (gradeitemrow) => {
    let action = 'disablegradeitem';
    let actiongradeitemid = gradeitemrow.dataset.gradeitemid;

    window.location.href = actionurlglobal + '&action=' + action + '&actiongradeitemid=' + actiongradeitemid;
};

const disableGradeitemCategory = (gradeitemrow) => {
    let action = 'disablegradeitemcategory';
    let actiongradeitemid = gradeitemrow.dataset.gradeitemid;
    if (gradeitemrow.textContent.includes("[[DISABLED]]")) {
        return;
    }
    // If the category is already disabled, don't do anything
    window.location.href = actionurlglobal + '&action=' + action + '&actiongradeitemid=' + actiongradeitemid;
};


const disableDragging = () => {
    let table = document.getElementById('gradeconfigwizard-dashboard-table');
    for (let element of table.querySelectorAll('tr')) {
        if (element.dataset.todragg == 'true') {
            element.draggable = false;
            let moveicon = element.querySelector('#move-icon-display');
            moveicon.classList.add("gradeconfigwizard-draggingcontent-color");
        }
    }
};

const enableDragging = () => {
    let table = document.getElementById('gradeconfigwizard-dashboard-table');
    let abletodragg = true;
    for (let element of table.querySelectorAll('tr')) {
        if (element.dataset.randomid != undefined && element.dataset.randomid != "") {
            abletodragg = false;
            break;
        }
    }

    if (abletodragg) {
        for (let element of table.querySelectorAll('tr')) {
            if (element.dataset.todragg == 'true') {
                element.draggable = true;
                let moveicon = element.querySelector('#move-icon-display');
                moveicon.classList.remove("gradeconfigwizard-draggingcontent-color");
            }
        }
    }
};

const addGradeitemCategoryAfterRow = (row) => {
    disableDragging();
    let gradecategoryid = row.dataset.gradecategoryid;
    let gradeitemid = row.dataset.gradeitemid;
    let depth = row.dataset.categorydepth;

    let newrandomid = generateUniqueId();
    let newdepth = parseInt(depth) + 1;

    let parentrelativepath = row.dataset.parentrelativepath;

    // In case the new category is a subcategory of an already created category
    if (parentrelativepath == undefined) {
        parentrelativepath = gradecategoryid;
    }
    // In case the new category is a subcategory of the course
    if (gradecategoryid == "" && depth == "0") {
        parentrelativepath = courseidglobal;
    }

    parentrelativepath = parentrelativepath + defaultPathSeparator + newrandomid;

    let newgradeitemcategoryrowhtml =
        gradeitemcategoryrowhtml(newrandomid, gradecategoryid, gradeitemid, newdepth, parentrelativepath);
    row.insertAdjacentHTML('afterend', newgradeitemcategoryrowhtml);

    let newgradeitemcategoryrow = document.getElementById(newrandomid);

    newgradeitemcategoryrow.querySelector('.remove-item-icon')
        .addEventListener('click', removeGradeitemCategoryButtonClickCallback);

    newgradeitemcategoryrow.querySelector('.add-gradeitemcategory-btn')
        .addEventListener('click', addGradeitemCategoryButtonClickCallback);
    newgradeitemcategoryrow.querySelector('.add-gradeitem-btn')
        .addEventListener('click', addGradeitemButtonClickCallback);
    newgradeitemcategoryrow.querySelector('.gradeitem-disable-btn').addEventListener('click', disableGradeitemButtonClickCallback);

    let newgradecategorytotaldepth = parseInt(newdepth) + 1;
    let newgradeitemcategorytotalrowhtml =
        gradeitemcategorytotalrowhtml(newrandomid, gradecategoryid, gradeitemid, newgradecategorytotaldepth);
    newgradeitemcategoryrow.insertAdjacentHTML('afterend', newgradeitemcategorytotalrowhtml);

    var newgradeitemcategorytotalrow =
        gradeconfigwizarddashboardtablebody.querySelector('[data-randomid="' + newrandomid + '"][data-istotal="1"]');

    newgradeitemcategoryrow.querySelector('.subcategoryname').addEventListener('input', function() {
        newgradeitemcategorytotalrow.querySelector('input.subcategoryname').value = 'Total ' + this.value;
    });

    return newgradeitemcategoryrow;
};

const addDashboardModalClasses = () => {
    let editcontent = document.getElementById('gradeconfigwizard-modal');
    editcontent.classList.add('modal');
    editcontent.classList.add('modal-content');
};

const removeDashboardModalClasses = () => {
    let editcontent = document.getElementById('gradeconfigwizard-modal');
    editcontent.classList.remove('modal');
    editcontent.classList.remove('modal-content');
};

const addDashboardBackdropModal = () => {
    let existentbackroundmodal = document.getElementById('background-gradeconfigwizardmodal');
    if (existentbackroundmodal === null) {
        let backgroundmodal = document.createElement('div');
        backgroundmodal.setAttribute('id', 'background-gradeconfigwizardmodal');
        backgroundmodal.classList.add('modal-backdrop');
        backgroundmodal.classList.add('show');
        let footer = document.getElementById('page-footer');
        footer.appendChild(backgroundmodal);
    }
};

const removeDashboardBackdropModal = () => {
    let backgroundmodal = document.getElementById('background-gradeconfigwizardmodal');
    let footer = document.getElementById('page-footer');
    if (backgroundmodal) {
        footer.removeChild(backgroundmodal);
    }
};

const addButtonsHiddenClass = () => {
    let buttonstop = document.getElementById('buttons-top');
    buttonstop.classList.add("hidden");
    let buttonsbottom = document.getElementById('buttons-bottom');
    buttonsbottom.classList.add("hidden");
};

const removeButtonsHiddenClass = () => {
    let buttonstop = document.getElementById('buttons-top');
    buttonstop.classList.remove("hidden");
    let buttonsbottom = document.getElementById('buttons-bottom');
    buttonsbottom.classList.remove("hidden");
};

const enableEditMode = () => {
    enableModal();
};

const disableEditMode = () => {
    disableModal();
};

const enableModal = () => {
    addDashboardModalClasses();
    addDashboardBackdropModal();
    removeButtonsHiddenClass();
};

const disableModal = () => {
    removeDashboardModalClasses();
    removeDashboardBackdropModal();
    addButtonsHiddenClass();
};

let dragged = null;
let target = null;
let draggingchilds = [];
let dropactionredirectinprogress = false;

// DRAG EVENTS FUNCTIONS
const dragstart = (event) => {
    // Make the row invisible until the dragend event is fired
    setTimeout(()=> {
        event.target.className = "invisible";
        }, 0);
    dragged = event.target;
    let under = false;

    if (dragged.dataset.itemtype == 'manual' || dragged.dataset.itemtype == 'mod') {
        return;
    }

    let array = Array.from(gradeconfigwizarddashboardtablebody.querySelectorAll('tr'));
    let element;
    for (element of array) {
        if (element.dataset.gradeitemid == dragged.dataset.gradeitemid) {
            under = true;
        }
        if (under && element.dataset.categorydepth > dragged.dataset.categorydepth) {
            // Modify the displaytype of the element to none
            //element.style.display = 'none';
            element.classList.add("gradeconfigwizard-draggingcontent");
            // And save the current elemnti into an array
            draggingchilds.push(element);
            if (element.dataset.gradeitemid == dragged.dataset.gradeitemid) {
                break;
            }
        }
    }
};

const dragend = (event) => {
    if (dropactionredirectinprogress === false) {
        for (let element of draggingchilds) {
            element.classList.remove("gradeconfigwizard-draggingcontent");
        }
        event.target.className = '';
    }
};

const dragover = (event) => {
    // Get the element that is being on the bottom of the dragged element
    // prevent default to allow drop
    event.preventDefault();

    target = event.target.closest('tr');
    target.className = 'big-separator';
};

const dragleave = (event) => {
    target = event.target.closest('tr');
    target.className = 'small-separator';
};

const drop = () => {
    let move = "after";
    if (target.dataset.istotal == '0'
        && target.dataset.itemtype == 'category'
        || target.dataset.itemtype == 'course') {
        move = "inside";
    }
    dropactionredirectinprogress = true;

    window.location.href = wwwrootglobal + '/grade/report/gradeconfigwizard/index.php'
        + '?id=' + courseidglobal
        + '&draggedid=' + dragged.dataset.gradeitemid
        + '&targetid=' + target.dataset.gradeitemid
        + '&move=' + move;
};

const removeGradeitemCategory = (gradeitemcategoryrow) => {

    let parentgradecategorydepth = gradeitemcategoryrow.dataset.categorydepth;
    let currentrow = gradeitemcategoryrow.nextElementSibling;
    while (currentrow) {
        let currentdepth = currentrow.dataset.categorydepth;
        if (parentgradecategorydepth >= currentdepth) {
            break;
        }
        if (currentrow.dataset.itemtype === 'category' && currentrow.dataset.istotal !== '1') {
            removeGradeitemCategory(currentrow);
        } else {
            removeGradeitemRow(currentrow);
        }
        currentrow = gradeitemcategoryrow.nextElementSibling;
    }
    gradeitemcategoryrow.remove();
    enableDragging();
};


/* END: FUNCTIONS */

/* START: CALLBACKS */

const gradeDashboardOnChangeCallback = () => {
    let dashboardeditingelements = document.querySelectorAll('#gradeconfigwizard-dashboard-table input.subcategoryname');
    if (dashboardeditingelements.length > 0) {
        enableEditMode();
    } else {
        disableEditMode();
    }
};

const targetNode = document.getElementById('gradeconfigwizard-dashboard-table');
const config = {attributes: true, childList: true, subtree: true};

const mode = (mutationList) => {
    for (const mutation of mutationList) {
        if (mutation.type === "attributes") {
            gradeDashboardOnChangeCallback();
        }
    }
};
const observer = new MutationObserver(mode);
observer.observe(targetNode, config);

const addGradeitemButtonClickCallback = (e) => {
    let targetrow = e.target.closest('tr');
    let newrow = addGradeitemAfterRow(targetrow);
    newrow.querySelector('input.subcategoryname').focus();
};

const removeGradeitemButtonClickCallback = (e) => {
    let targetrow = e.target.closest('tr');
    removeGradeitemRow(targetrow);
};

const disableGradeitemButtonClickCallback = (e) => {
    let targetrow = e.target.closest('tr');
    if (targetrow.dataset.itemtype === 'mod' || targetrow.dataset.itemtype === 'manual') {
        disableGradeitem(targetrow);
    } else if (targetrow.dataset.itemtype === 'category') {
        disableGradeitemCategory(targetrow);
    }
};


const addGradeitemCategoryButtonClickCallback = (e) => {
    let targetrow = e.target.closest('tr');
    let newrow = addGradeitemCategoryAfterRow(targetrow);
    newrow.querySelector('input.subcategoryname').focus();
};

const removeGradeitemCategoryButtonClickCallback = (e) => {
    let targetrow = e.target.closest('tr');
    removeGradeitemCategory(targetrow);
};

/* END: CALLBACKS */

/* START: EVENT LISTENERS DECLARATION */

export const init = (courseid, urlformulacreator, gradeitemid, wwwroot, actionurl) => {
    courseidglobal = courseid;
    urlformulacreatorglobal = urlformulacreator;
    gradeitemidglobal = gradeitemid;
    wwwrootglobal = wwwroot;
    actionurlglobal = actionurl;
    gradeconfigwizarddashboardtablebody.querySelectorAll('.add-gradeitemcategory-btn').forEach(
        function(row) {
            row.addEventListener('click', addGradeitemCategoryButtonClickCallback);
        }
    );

    gradeconfigwizarddashboardtablebody.querySelectorAll('.add-gradeitem-btn').forEach(
        function(row) {
            row.addEventListener('click', addGradeitemButtonClickCallback);
        }
    );

    gradeconfigwizarddashboardtablebody.querySelectorAll('.gradeitem-disable-btn').forEach(
        function(row) {
            row.addEventListener('click', disableGradeitemButtonClickCallback);
        }
    );

// Drag events listeners
    gradeconfigwizarddashboardtablebody.querySelectorAll('tr').forEach(
        function(row) {
            row.addEventListener('dragstart', dragstart);
            row.addEventListener('dragend', dragend);
            row.addEventListener('dragover', dragover);
            row.addEventListener('dragleave', dragleave);
            row.addEventListener('drop', drop);
        }
    );
};

/* END: EVENT LISTENERS DECLARATION */
