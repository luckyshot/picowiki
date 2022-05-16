(function(CodeMirror, Markdown) {
	"use strict";

	var converter = new Markdown.Converter();
	Markdown.Extra.init(converter);

	CodeMirror.defineOption("preview", false, function(cm, val, old) {
		if (old == CodeMirror.Init) old = false;
		if (!old == !val) return;
		if (val) {
			setPreview(cm);
		} else {
			setNormal(cm);
		}
	});

	function setPreview(cm) {
		var wrap = cm.getWrapperElement();
		wrap.className += " CodeMirror-has-preview";

		refreshPreview(wrap, cm);
	}

	function refreshPreview(wrap, cm) {
		var previewNodes = wrap.getElementsByClassName("CodeMirror-preview");
		var previewNode;
		if(previewNodes.length == 0) {
			var previewNode = document.createElement('div');
			previewNode.className = "CodeMirror-preview";
			wrap.appendChild(previewNode);
		} else {
			previewNode = previewNodes[0];
		}
		previewNode.innerHTML = converter.makeHtml(cm.getValue());
	}

	function setNormal(cm) {
		var wrap = cm.getWrapperElement();
		wrap.className = wrap.className.replace(/\s*CodeMirror-has-preview\b/, "");
		cm.refresh();
	}
})(CodeMirror, Markdown);

/**
 * @license MirrorMark v0.1
 * (c) 2015 Musicbed http://www.musicbed.com
 * License: MIT
 */
(function(CodeMirror) { 'use strict';
	/**
	 * Bootstrap our module
	 */
	(function(fn) {
		if (typeof exports == "object" && typeof module == "object") { // CommonJS
		  module.exports = fn;
		} else if (typeof define == "function" && define.amd) { // AMD
		  return define([], fn);
		}

		if (window) window.mirrorMark = fn
	})(mirrorMark);

	/**
	 * Merge
	 *
	 *
	 * @param  {Object}		object The object to merge into
	 * @param  {Object/Array}  source The object or array of objects to merge
	 * @return {Object}		 The original object
	 */
	function merge(object, source) {
		if(Array.isArray(source)) {
			for(var i = sources.length - 1; i >= 0; i--) {
				merge(object, source[i]);
			}
		} else {
			for (var attrname in source) {
				object[attrname] = source[attrname];
			}
		}

		return object;
	}

	/**
	 * Our delegate prototype used by our factory
	 * @type {Object}
	 */
	var mirrorMarkProto = {

		/**
		 * Render the component
		 */
		render: function render() {
			this.registerKeyMaps(this.keyMaps);
			this.cm = CodeMirror.fromTextArea(this.element, this.options);

			if (this.options.showToolbar) {
			  this.setToolbar(this.tools);
			}
		},

		/**
		 * Setup the toolbar
		 */
		setToolbar: function setToolbar(tools) {

			var toolbar = document.createElement('ul');
			toolbar.className = this.options.theme + '-' + 'toolbar';

			var tools = this.generateToolList(tools);

			tools.forEach(function(tool) {
				toolbar.appendChild(tool)
			});

			var cmWrapper = this.cm.getWrapperElement();
			cmWrapper.insertBefore(toolbar, cmWrapper.firstChild);
		},

		/**
		 * Register Keymaps by extending the extraKeys object
		 * @param {Object} keyMaps
		 */
		registerKeyMaps: function registerKeyMaps(keyMaps) {
			for (var name in keyMaps) {
				if (typeof(this.actions[keyMaps[name]]) !== 'function') throw "MirrorMark - '" + keyMaps[name] + "' is not a registered action";

				var realName = name.replace("Cmd-", (CodeMirror.keyMap["default"] == CodeMirror.keyMap.macDefault) ? "Cmd-" : "Ctrl-");
				this.options.extraKeys[realName] = this.actions[keyMaps[name]].bind(this)
			}
		},


		/**
		 * Register actions by extending the default actions
		 * @param  {Object} actions [description]
		 */
		registerActions: function registerActions(actions) {
			return merge(this.actions, actions);
		},


		/**
		 * Register tools by extending and overwriting the default tools
		 * @param  {Array} tools
		 * @param  {Bool} replace - replace the default tools with the ones provided. Defaults to false.
		 */
		registerTools: function registerTools(tools, replace) {
			for (var action in tools) {
				if (this.actions[tools[action].action] && typeof(this.actions[tools[action].action]) !== 'function') throw "MirrorMark - '" + tools[action].action + "' is not a registered action";
			}

			if (replace) {
				this.tools = tools;
				return;
			}

			this.tools = this.tools.concat(tools)
		},

		/**
		 * A recursive function to generate and return an unordered list of tools
		 * @param  {Object}
		 */
		generateToolList: function generateToolList(tools) {
			return tools.map(function(tool) {
				var item = document.createElement("li"),
					anchor = document.createElement("a");

				item.className = tool.name;

				if (tool.className) {
					anchor.className = tool.className;
				}

				if (tool.showName) {
					var text = document.createTextNode(tool.name);
					anchor.appendChild(text);
				}

				if (tool.action) {
					anchor.onclick = function(e) {
						this.cm.focus();
					 	this.actions[tool.action].call(this);
					}.bind(this);
				}

				item.appendChild(anchor);

				if (tool.nested) {
					item.className += " has-nested";
					var ul = document.createElement('ul');
						ul.className = this.options.theme + "-toolbar-list"
					var nested = generateToolList.call(this, tool.nested);
						nested.forEach(function(nestedItem) {
							ul.appendChild(nestedItem);
						});

					item.appendChild(ul);
				}

				return item

			}.bind(this));
		},

		/**
		 * Default Tools in Toolbar
		 */
		tools: [
			{ name: "bold", action: "bold" },
			{ name: "italicize", action: "italicize" },
			{ name: "blockquote", action: "blockquote" },
			{ name: "strikethrough", action: "strikethrough" },
			{ name: "link", action: "link" },
			{ name: "image", action: "image" },
			{ name: "unorderedList", action: "unorderedList" },
			{ name: "orderedList", action: "orderedList" },
			{ name: "fullScreen", action: "fullScreen" },
			{ name: "preview", action: "preview" },
		],

		/**
		 * Default Keymaps
		 * @type {Object}
		 */
		keyMaps: {
			"Cmd-B": 'bold',
			"Cmd-I": 'italicize',
			"Cmd-'": 'blockquote',
			"Cmd-Alt-L": 'orderedList',
			"Cmd-L": 'unorderedList',
			"Cmd-Alt-I": 'image',
			"Cmd-H": 'hr',
			"Cmd-K": 'link',
			"F11": "fullScreen",
			"Esc": "exitFullScreen",
		},

		/**
		 * Default Actions
		 * @type {Object}
		 */
		actions: {
			bold: function () {
				this.toggleAround('**', '**')
			},
			italicize: function () {
				this.toggleAround('*', '*')
			},
			strikethrough: function () {
				this.toggleAround('~~', '~~')
			},
			"code": function () {
				this.toggleAround('```\r\n', '\r\n```')
			},
			"blockquote": function () {
				this.toggleBefore('> ');
			},
			"orderedList": function () {
				this.toggleBefore('1. ');
			},
			"unorderedList": function () {
				this.toggleBefore('* ');
			},
			"image": function () {
				this.toggleAround('![', '](http://)');
			},
			"link": function () {
				this.toggleAround('[', '](http://)');
			},
			"hr": function () {
				this.insert('---');
			},
			"fullScreen": function () {
				var fullScreen = !this.cm.getOption("fullScreen");

				// You must turn off scrollPastEnd on after going full screen
				// and before exiting it
				if(!fullScreen) this.cm.setOption("scrollPastEnd", fullScreen)
				this.cm.setOption("fullScreen", fullScreen);
				if(fullScreen) this.cm.setOption("scrollPastEnd", fullScreen)
			},
			"exitFullScreen": function() {
				if (this.cm.getOption("fullScreen")) {
					this.cm.setOption("scrollPastEnd", fullScreen)
					this.cm.setOption("fullScreen", false);
				}
			},
			"preview": function() {
				this.cm.setOption("preview", !this.cm.getOption("preview"));
			}
		},

		/**
		 * Insert a string at cursor position
		 * @param  {String} insertion
		 */
		insert: function insert(insertion) {
			var doc = this.cm.getDoc();
			var cursor = doc.getCursor();

			doc.replaceRange(insertion, { line: cursor.line, ch: cursor.ch });
		},

		/**
		 * Toggle a string at the start and end of a selection
		 * @param  {String} start Start string to wrap
		 * @param  {String} end  End string to wrap
		 */
		toggleAround: function toggleAround(start, end) {
			var doc = this.cm.getDoc();
			var cursor = doc.getCursor();

			if (doc.somethingSelected()) {
				var selection = doc.getSelection();
				if(selection.startsWith(start) && selection.endsWith(end)) {
					doc.replaceSelection(selection.substring(start.length, selection.length - end.length), "around");
				} else {
					doc.replaceSelection(start + selection + end, "around");
				}
			} else {
				// If no selection then insert start and end args and set cursor position between the two.
				doc.replaceRange(start + end, { line: cursor.line, ch: cursor.ch });
				doc.setCursor({ line: cursor.line, ch: cursor.ch + start.length })
			}
		},

		/**
		 * Toggle a string before a selection
		 * @param {String} insertion	String to insert
		 */
		toggleBefore: function toggleBefore(insertion) {
			var doc = this.cm.getDoc();
			var cursor = doc.getCursor();

			if (doc.somethingSelected()) {
				var selections = doc.listSelections();
				var remove = null;
				this.cm.operation(function() {
					selections.forEach(function(selection) {
						var pos = [selection.head.line, selection.anchor.line].sort();

						// Remove if the first text starts with it
						if(remove == null) {
							remove = doc.getLine(pos[0]).startsWith(insertion);
						}

						for (var i = pos[0]; i <= pos[1]; i++) {
							if(remove) {
								// Don't remove if we don't start with it
								if(doc.getLine(i).startsWith(insertion)) {
									doc.replaceRange("", { line: i, ch: 0 }, {line: i, ch: insertion.length});
								}
							} else {
								doc.replaceRange(insertion, { line: i, ch: 0 });
							}
						}
					});
				});
			} else {
				var line = cursor.line;
				if(doc.getLine(line).startsWith(insertion)) {
					doc.replaceRange("", { line: line, ch: 0 }, {line: line, ch: insertion.length});
				} else {
					doc.replaceRange(insertion, { line: line, ch: 0 });
				}

			}
		}
	}

	/**
	 * Our Factory
	 * @param  {Object} element
	 * @param  {Object} options
	 * @return {Object}
	 */
	function mirrorMark(element, options) {

		// Defaults
		var defaults = {
			mode: 'gfm',
			theme: 'default mirrormark',
			tabSize: '2',
			indentWithTabs: true,
			lineWrapping: true,
			autoCloseBrackets: true,
			autoCloseTags: true,
			addModeClass: true,
			showToolbar: true,
			extraKeys: {
				"Enter": 'newlineAndIndentContinueMarkdownList',
			},
		}

		// Extend our defaults with the options provided
		merge(defaults, options);

		return merge(Object.create(mirrorMarkProto), { element: element, options: defaults });
	}

})(window.CodeMirror);
