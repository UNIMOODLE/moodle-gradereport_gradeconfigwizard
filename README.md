# Grade setup wizard
The 'Grade Setup Wizard' plugin allows you to perform certain basic tasks in setting the Gradebook quickly and easily. The goal of the plugin is to facilitate the organization of the Gradebook in complex situations. At a logical level, the changes made both in the dashboard and in the formula editors and evaluation schemes are applied in the native Moodle Gradebook, either by creating elements, modifying the organization of categories and subcategories, or creating formulas.

The plugin is made up of 3 components:

- **Dashboard**. The dashboard allows access to the main functionalities in relation to editing formulas and editing schemes, in addition to enabling a drag&drop system to move elements as well as the possibility of disabling elements with a single click.
- **Improved formula editor**. The formula editor allows you to generate simple formulas based on aggregation methods by selecting elements, aggregation method and indicating, if necessary, the weights of the formula elements.
- **Evaluation schemes**. Access to the creation of two types of evaluation schemes common in the university world, which can be created in a quick and intuitive way.



## How to access the 'Grade Setup Wizard'?
To see the 'Grading Setup Wizard', which is the main element to use all the functionalities, it is necessary:

1. Navigate to the course.
2. Go to the 'Grades' section.
3. Select 'Grade Setup Wizard' from the drop-down menu.

## Dashboard
The dashboard of the aims to centralize the different utilities of the plugin, in addition to allowing some functionalities in a fast and intuitive way.

The 'Grade Setup Wizard' dashboard view is a replica of the configuration done in the native Moodle Gradebook. Any changes applied to the Gradebook will appear in the dashboard upon next access, just as any changes made to the dashboard will be replicated in the native Moodle Gradebook.

## Improved Formula Editor
The '**Improved Formula Editor**' allows you to create formulas based on aggregation methods in a simple and intuitive way. To access it, you must display the 'Edit' menu for category totals or manual rating items and click 'Edit calculation'.

## Multiple Assessment Editor
The '**Multiple Assessment Editor**' makes it easy to create a Gradebook with weighted elements and a cut-off grade for retrieval. To access the editor you must go to the dashboard and click on the 'Access to the multiple evaluations editor' option.

## Assessment Pathway Editor
The '**Assessment Pathway Editor**' facilitates the creation of a Gradebook with several assessment pathways for the same subject. To access the editor you must go to the top of the dashboard and click on the 'Access to the assessment pathway editor' option.

## Installation
You can download the admin tool plugin from: https://github.com/UNIMOODLE/moodle-gradereport_gradeconfigwizard

The grade report should be located and named as:

`[yourmoodledir]/grade/report/gradeconfigwizard`
    
## Uninstall
1. Remove the `gradereport_gradeconfigwizard` plugin from the Moodle folder: `[yourmoodledir]/grade/report/gradeconfigwizard`
2. Access the plugin uninstall page: `Site Administration > Plugins > Plugins overview`
3. Look for the removed plugin and click on uninstall.
## Authors

Project implemented by the &quot;Recovery, Transformation and Resilience Plan.
Funded by the European Union - Next GenerationEU.

Produced by the UNIMOODLE University Group: Universities of
Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

Display information about all the gradereport_gradeconfigwizard modules in the requested course.

* @package gradeconfigwizard
* @copyright 2023 Proyecto UNIMOODLE
* @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Joan Carbassa (IThinkUPC) &lt;joan.carbassa@ithinkupc.com&gt;
 * @author Yerai Rodríguez (IThinkUPC) &lt;yerai.rodriguez@ithinkupc.com&gt;
 * @author Marc Geremias (IThinkUPC) &lt;marc.geremias@ithinkupc.com&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;

## License
 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.