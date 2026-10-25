"use strict";

xx.module("myExpl", function (apod) {

  // const vars = xx.vars;
  const q = xx.q;
  let common;
  xx.imports.push(function () {
    common = xx.common;
  });


  let folders;   // variable to hold the folderClass
  let onFolder;  // the folder that the mouse is on
  let displaceLeft; // items in folder_div to be moved left if no sub-folders
  let save_ai_id = -1;   // saves the id of the chosen folder
  let save_json_files;      // saves the json.files of the chosen folder
  let fname;    // full name of the file selected
  let save_selected_file = '';  // save the full name of the selected file
  let singleclickdone; // needed for checking double click when opening a file
  let show_dir_from = 1;   // 0 = root dir, 1 = first subfolder of root dir
  let show_only_one = false;  // false = show all top level dirs, true = show only on top level folder
  let onlyFiles = '';  // false = show all top level dirs, true = show only on top level folder
  let subsLevel = 65000;  // the level to which subs are shown
  let callback;  // function to call on exit

  function init() {
    addAwkwardEventListeners();  // for some awkward myExplorer event types

    window.onscroll = function (e) {
      q.g('folderCover').classList.add('nodisplay');
    };

    q.g("myExplorer").addEventListener("click", function (e) {
      e.stopPropagation();
      e.preventDefault();
      const target = e.target;
      if (target.parentNode.id === 'folderCover' || target.id === 'folderCover') {
        let item = onFolder.parentNode;
        let ai_id = Number(item.id.slice(3));
        let selected = folders.getSelected();
        if ((selected === 0 || selected > 0) && ai_id === save_ai_id) return; // same folder so return
        // turn folderCover > span.itemName on, highlight folder
        let ele = this.querySelector("span.itemName");
        if (ele) ele.classList.add("chosen"); // ele will be null at start-up so test
        // turn the folder that is now selected on and old folder off
        folders.setSelected(ai_id);
        let fp = folders.fullPath(ai_id);
        if (ai_id === save_ai_id) {
          q.g('files_div').innerHTML = filesList(fp, save_json_files);
        } else {
          // a new folder so clear and save the new folder id
          set_selected_off();
          save_ai_id = ai_id;
          q.g('files_div').innerHTML = '';
          // fetch the files in the folder and show them in file
          return new Promise((resolve, reject) => {
            // get the files in the folder clicked, ie fp, and show them in files_div
            common.doFetch("fr.php", "getFiles", { iFile: 'myExpl', dir: fp, only: onlyFiles })
              .then((json) => {
                if (json.value === "success") {
                  save_json_files = json.files;
                  q.g('files_div').innerHTML = filesList(fp, save_json_files);
                } else {
                  if (json.errmsg) {
                    alert(json.errmsg);
                  } else {
                    alert("There was a problem with Fetch call");
                  }
                }
                resolve();
              })
              .catch((err) => {
                console.log('an error occurred when fetching the files');
                alert('error when fetching files')
              });
          });
        }
      } else if (target.classList.contains('inbox')) {
        let svg = target.parentNode;
        let doorClosed = svg.getAttribute("data-closed");
        if (doorClosed === "true") {
          // + sign, ie closed, so open folder
          let items = svg.parentNode.parentNode;
          svg.querySelector("line.down").classList.add("nodisplay");
          svg.setAttribute("data-closed", false);
          items.nextElementSibling.classList.remove("nodisplay");
          items.querySelector('.holder>span').classList.add('folder_open');
        } else if (doorClosed === "false") {
          // - sign, ie open, so close folder
          // check if this folder which is been closed contains the chosen folder and if so then I must turn the chosen folder off
          let items = svg.parentNode.parentNode;
          let selected = folders.getSelected();
          if (selected === 0 || selected > 0) {
            let thisid = Number(items.id.slice(3));
            if (folders.isADecendentOf(selected, thisid)) {
              // the currently selected folder is a decendent of the folder that is now being closed, so make selected be -1 and turn chosen off
              folders.setSelected(-1);
              // remove files from files_div
              q.g("files_div").innerHTML = "";
            }
          }
          svg.querySelector("line.down").classList.remove("nodisplay");
          svg.setAttribute("data-closed", true);
          items.nextElementSibling.classList.add("nodisplay");
          items.querySelector('.holder>span').classList.remove('folder_open');
        }
      } else if (target.tagName.toUpperCase() === 'BUTTON') {
        let butt = target.textContent;
        if (butt == "Reset") {
          // clear everything and reput the folders
          set_selected_off();
          folders.putFob();
          // highlight the top folder and then trigger a click on it
          onFolder = q.qs(".holder", "folders_div"); // will find the first span.holder element
          q.trigger("folderCover", "click");
          return;
        }
        // butt is either 'Open' or 'Cancel'
        if (butt === 'Open' && q.g("fn_input").value === "") return;   // no file selected
        returnToCaller(butt);
      } else {
        const parent = target.parentNode;
        if (parent.tagName.toUpperCase() === 'LI') {
          fname = parent.dataset['fp'];
          q.g("fn_input").value = fname.substring(fname.lastIndexOf("/") + 1);
          if (fname != save_selected_file) {
            save_selected_file = fname;
            q.removeAllClasses("#files_div li span", "chosen");
            target.classList.add("chosen");
          }
          singleclickdone = true;
        }
      }
      function filesList(fp, arr) {
        let ss = `<ul class="fileList" data-fp="${fp}">`;
        for (let i = 0; i < arr.length; i = i + 2) {
          ss += `<li data-fp="${fp}${arr[i]}"><span class="ext_${arr[i + 1]}">
        ${arr[i]}</span></li>`;
        }
        return ss + "</ul>";
      }
    });

    q.g("myExplorer").addEventListener("dblclick", function (e) {
      e.stopPropagation();
      e.preventDefault();
      if (e.target.parentNode.tagName.toUpperCase() === 'LI') {
        if (singleclickdone === true) {
          returnToCaller("Open");
        }
      }
    });

    function returnToCaller(butt) {
      // turn highlight on file off
      q.removeAllClasses("#files_div li span", "chosen");
      // clear values because myExplorer will be closed
      let tmp = fname;
      set_selected_off();
      // send button clicked and the selected filename back to the caller
      callback(butt, tmp);
    }

    function set_selected_off() {
      // clears all values in myExplorer
      q.g("fn_input").value = "";
      save_ai_id = -1;
      fname = '';
      save_selected_file = '';
    }

  }  // end if init()


  // ************************************************************************
  // start of foldersClass which has methods to get the folders and put them
  // ************************************************************************
  class foldersClass {
    constructor(data) {
      this.data = data;
      this.selected = -1;
      this.parr = [];
      this.fob = '';
    }

    getFob() {
      return new Promise((resolve, reject) => {
        this.data.iFile = 'myExpl';
        common.doFetch("fr.php", "getFolders", this.data)
          .then((json) => {
            this.fob = json;
            this.fix_parr();
            resolve();
          });
      });
    }

    putFob() {
      q.g("folders_div").innerHTML = this.theFolders(this.fob);
      // if no subfolders then move items left because there is no + for subfolders
      if (displaceLeft) {
        const eles = q.g("folders_div").querySelectorAll(".level0");
        Array.from(eles).forEach(ele => {
          ele.style.marginLeft = -14 + "px";
        });
      }
    }

    theFolders(json) {
      // turn folderCover off just in case it is still showing
      let ele = q.g("folderCover");
      ele.classList.remove("chosen");
      ele.classList.add("nodisplay");
      try {
        if (typeof show_dir_from === 'string') {
          show_dir_from = json.f.indexOf(show_dir_from);
          if (!show_dir_from || show_dir_from < 0) return '';
        }
        displaceLeft = true;
        return this.jsFolders(json, show_dir_from, 0);
      } catch (e) {
        alert(`error : ${e}`);
      }
    }

    // Note that the spans are put together to prevent space gaps between the elements
    jsFolders(json, p, level) {
      let s = '', s1;
      while (true) {
        s1 = `class="items"`;
        s += `<div class="level${level}">`;
        if (json.ps[p] > 0 && level < subsLevel) {
          if (level === 0 && displaceLeft) displaceLeft = false;
          s += `<div ${s1} id="ai_${p}"}>
          <span class="indx">${common.getHtml("tem_plusMinus")}</span><span class="holder"><span class="directory"></span><span class="itemName">${json.f[p]}</span></span>
          </div>
          <div id="sm_${p}" class="subcatlistdiv nodisplay">
            ${this.jsFolders(json, json.ps[p], level + 1)}
          </div>`;
        } else {
          s += `<div ${s1} id="ai_${p}">
          <span class="holder right16"><span class="directory"></span><span class="itemName">${json.f[p]}</span></span>
          </div>`;
        }
        s += '</div>';
        if (level === 0 && show_only_one === true) return s;
        p = json.pn[p];
        if (p === 0) return s;
      }
    }

    setSelected(n) {
      if (this.selected === 0 || this.selected > 0) {
        const ele = q.g("ai_" + this.selected);
        if (ele) ele.querySelector("span.itemName").classList.remove("chosen");
      }
      this.selected = n;
      if (n === 0 || n > 0)
        q.g("ai_" + this.selected).querySelector("span.itemName").classList.add("chosen");
    }

    getSelected() {
      return this.selected;
    }

    fix_parr() {
      this.parr[0] = 0;
      this.parr_fixsub(0);
    }

    parr_fixsub(p) {
      let pp = this.fob.ps[p];
      while (pp != 0) {
        this.parr[pp] = p;
        if (this.fob.ps[pp] > 0) this.parr_fixsub(pp);
        pp = this.fob.pn[pp];
      }
    }

    // This function returns true if idn1 is a decendent of idn2
    isADecendentOf(idn1, idn2) {
      let p = idn1;
      while (p > 0) {
        p = this.parr[p];
        if (p === idn2) return true;
      }
      return false;
    }

    fullPath(id) {
      let f = this.fob.f;
      let s = "";
      while (id != 0) {
        s = f[id] + "/" + s;
        id = this.parr[id];
      }
      return this.data.dir + s;
    }
  } // end of folderClass


  // ************************************************************************
  // start of foldersClass which has methods to get the folders and put them
  // ************************************************************************
  class myExplClass {
    constructor(ob) {
      this.callback = ob.callback;
      callback = this.callback;
      if (!callback) alert('no callback');
      this.dirPath = ob.dirPath || '';
      this.notdirs = ob.notdirs || '';
      if (typeof ob.show_dir_from === 'string' || ob.show_dir_from >= 0) {
        show_dir_from = ob.show_dir_from;
      } else {
        show_dir_from = 1;
      }
      // show_dir_from = 0;  // for testing only, shows the rootDir
      show_only_one = ob.show_only_one || false;
      onlyFiles = ob.onlyFiles || '';
      subsLevel = (ob.subsLevel >= 0) ? ob.subsLevel : 65000;
      // if (ob.subsLevel >= 0) subsLevel = ob.subsLevel; else subsLevel = 65000;
      this.fetchTheFolders()
        .then(() => {
          this.putExplorer();
        });
    }

    saveHtml() {
      this.html = q.g('myExplorer').innerHTML;
    }

    putHtml() {
      callback = this.callback;
      q.g('myExplorer').innerHTML = this.html;
      addAwkwardEventListeners();  // needed again here because html was replaced
    }

    fetchTheFolders() {
      return new Promise((resolve, reject) => {
        this.folders = new foldersClass({ dir: this.dirPath, notdirs: this.notdirs });
        this.folders.getFob().then(() => {
          folders = this.folders;
          resolve();
        });
      });
    }

    putExplorer() {
      this.folders.putFob();
      // set onFolder to the first span.holder element, and trigger a click on it
      onFolder = q.qs(".holder", "folders_div"); // will find the first span.holder element
      if (!onFolder) return;
      q.trigger("folderCover", "click");
    }
  }  // end of myExplClass



  function addAwkwardEventListeners() {
    // these elements in myExplorer have awkward types so I don't want to put them under the myExplorer event listener for those types - elements inside myExplorer get replaced so they must be added again if they are not included in the myExplorer event listener

    q.g("folders_div").addEventListener("mouseover", function (e) {
      const target = e.target.parentNode;
      if (target.classList.contains('holder')) {
        let ele = q.qs("#folderCover");
        const offs = q.containerOffset(target, q.g('myExplorer'));
        // const offs = q.bodyOffset2(target, q.g('myExplorer'));
        ele.style.left = offs[1] + "px";
        ele.style.top = offs[0] + "px";
        ele.innerHTML = common.htmlspecialchars_decode(target.innerHTML);
        ele.classList.remove("nodisplay");
        onFolder = target;
      }
    });

    /* 
    The next 2 events turn off folderCover if 
    a) the mouse exited folderCover or 
    b) the mouse exited the underlying span.holder.
     */
    q.g("folderCover").addEventListener("mouseout", function __mouseoutfolderCover(e) {
      if (q.isChildOf(this, e.relatedTarget)) return;
      this.classList.add("nodisplay");
    });

    // Note that this might not be needed because once the mouse enters span.holder
    // then div#folderCover covers the span.holder element. But I have found folderCover
    // might not completely cover span.holder - I don't know why so I have put this
    // here just in case.
    q.g("folders_div").addEventListener("mouseout", function (e) {
      e.stopPropagation();
      e.preventDefault();
      const target = e.target;
      if (target.classList.contains('holder')) {
        if (e.relatedTarget.parentNode.id == "folderCover") return;
        if (e.relatedTarget.id == "folderCover") return;
        q.g("folderCover").classList.add("nodisplay");
      }
    });

    q.g('folders_div').onscroll = function (e) {
      q.g('folderCover').classList.add('nodisplay');
    };

    q.g('folderCover').onwheel = function (e) {
      q.g('folderCover').classList.add('nodisplay');
    };

  }  // end of addAwkwardEventListeners


  apod.extend({
    init: init,
    myExplClass: myExplClass,
  });


});
