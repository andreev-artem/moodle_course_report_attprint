/**
 * Client-side JavaScript for attprint report
 * based on client-side JavaScript for group management interface.
 * 
 */


/**
 * Class UpdatableMembersCombo
 */
function UpdatableMembersCombo(wwwRoot, courseId) {
    this.wwwRoot = wwwRoot;
    this.courseId = courseId;

    this.connectCallback = {
        success: function(o) {

        	if (o.responseText !== undefined) {
                var selectEl = document.getElementById("members");
        	    if (selectEl && o.responseText) {
                    var roles = eval("("+o.responseText+")");

                    // Clear the members list box.
                    if (selectEl) {
                        while (selectEl.firstChild) {
                            selectEl.removeChild(selectEl.firstChild);
                        }
                    }
                    // Populate the members list box.
                    for (var i=0; i<roles.length; i++) {
                        var optgroupEl = document.createElement("optgroup");
                        optgroupEl.setAttribute("label",roles[i].name);

                        for(var j=0; j<roles[i].users.length; j++) {
                            var optionEl = document.createElement("option");
                            optionEl.setAttribute("value", roles[i].users[j].id);
                            optionEl.title = roles[i].users[j].name;
                            optionEl.innerHTML = roles[i].users[j].name;
                            optgroupEl.appendChild(optionEl);
                        }
                        selectEl.appendChild(optgroupEl);
                    }
                }
        	}
        	// Remove the loader gif image.
        	removeLoaderImgs("membersloader", "memberslabel");
        },

        failure: function(o) {
            removeLoaderImgs("membersloader", "memberslabel");
        }

    };

    // Hide the updatemembers input since AJAX will take care of this.
    YAHOO.util.Dom.setStyle("updatemembers", "display", "none");
}

/**
 * When a group is selected, we need to update the members.
 * The Add/Remove Users button also needs to be disabled/enabled
 * depending on whether or not a group is selected
 */
UpdatableMembersCombo.prototype.refreshMembers = function () {

    // Get group selector and check selection type
    var selectEl = document.getElementById("groups");
    var selectionCount=0,groupId=0;
    if( selectEl ) {
        for (var i = 0; i < selectEl.options.length; i++) {
            if(selectEl.options[i].selected) {
                selectionCount++;
                if(!groupId) {
                    groupId=selectEl.options[i].value;
                }
            }
        }
      }
    var singleSelection=selectionCount == 1;

    // Add the loader gif image (we only load for single selections)
    if(singleSelection) {
        createLoaderImg("membersloader", "memberslabel", this.wwwRoot);
    }

    // Update the label.
    var spanEl = document.getElementById("thegroup");
    if (singleSelection) {
        spanEl.innerHTML = selectEl.options[selectEl.selectedIndex].title;
    } else {
        spanEl.innerHTML = '&nbsp;';
    }

    // Clear the members list box.
    selectEl = document.getElementById("members");
    if (selectEl) {
        while (selectEl.firstChild) {
            selectEl.removeChild(selectEl.firstChild);
        }
    }

    if(singleSelection) {
        var sUrl = this.wwwRoot+"/course/report/attprint/index.php?id="+this.courseId+"&group="+groupId+"&act_ajax_getmembersingroup";
        YAHOO.util.Connect.asyncRequest("GET", sUrl, this.connectCallback, null);
    }
};



var createLoaderImg = function (elClass, parentId, wwwRoot) {
    var parentEl = document.getElementById(parentId);
    if (!parentEl) {
        return false;
    }
    if (document.getElementById("loaderImg")) {
        // A loader image already exists.
        return false;
    }
    var loadingImg = document.createElement("img");

    loadingImg.setAttribute("src", wwwRoot+"/pix/i/ajaxloader.gif");
    loadingImg.setAttribute("class", elClass);
    loadingImg.setAttribute("alt", "Loading");
    loadingImg.setAttribute("id", "loaderImg");
    parentEl.appendChild(loadingImg);

    return true;
};


var removeLoaderImgs = function (elClass, parentId) {
    var parentEl = document.getElementById(parentId);
    if (parentEl) {
        var loader = document.getElementById("loaderImg");
        parentEl.removeChild(loader);
    }
};
