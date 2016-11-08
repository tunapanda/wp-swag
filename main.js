jQuery(function($) {

	function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) {
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) {
				return decodeURIComponent(sParameterName[1]);
			}
		}
	}

	$(document).ready(function() {
		var linkUrl;

		$(".swag-admin-link").click(function() {
			linkUrl = $(this).attr("href");
			var confirmId = $(this).attr("confirm-id");
			$("#" + confirmId).show();
			return false;
		});

		$(".swag-admin-close").click(function() {
			$(".swag-admin-confirm").hide();
		});

		$(".swag-admin-ok").click(function() {
			$(".swag-admin-confirm").hide();
			location.href = linkUrl;
		});
	});

	$(document).ready(function() {
		var fill_ghu_uri = getUrlParameter("fill_ghu_uri");

		if (fill_ghu_uri)
			$('[name="github_updater_repo"]').val(fill_ghu_uri);
	});

	// Button to show swagpaths that the user is not yet prepared for.
	$(document).ready(function() {
		$(".view-unprepared").click(function() {
			$(".unprepared").removeClass("unprepared");
			$(".after.listing").hide();
			return false;
		});
	});

	// Update tickmark image on completed swagifact.
	$(document).ready(function() {
		var shownCompletedScreen;

		$(document).on("h5pXapiStatementSaved", function(e) {
			if (e.message.swagpathComplete) {
				var imgUri = PLUGIN_URI + "/img/badge.png";
				$(".swagpath-badge").attr("src", imgUri);

				if (!shownCompletedScreen) {
					shownCompletedScreen = true;
					$(".swagpath-completed").fadeIn();
				}
			}

			if (e.message.swagifactComplete) {
				var imgUri = PLUGIN_URI + "/img/completed-logo.png";
				$("ul.content-tab-list li.selected a img.coursepresentation").attr("src", imgUri);
			}
		});

		$(".swagpath-action-close").click(function() {
			$(".swagpath-completed").hide();
			return false;
		});

		$(".swagpath-action-swagmap").click(function() {
			location.href = HOME_URL + "/swag/map";
			return false;
		});

		$(".swagpath-action-tracks").click(function() {
			location.href = HOME_URL + "/swag/toc?track=" + TRACK_SLUG;
			return false;
		});

		$(".swagpath-action-badges").click(function() {
			location.href = HOME_URL + "/my-account/";
			return false;
		});
	});

	// Initialize swagmap.
	$(document).ready(function() {
		if (!$("#swagmapcontainer").length)
			return;

		$("#swagmapcontainer").height(($(window).height() - 120) + "px");

		var width = $("#swagmapcontainer").width(),
			height = $("#swagmapcontainer").height();

		var force = d3.layout.force()
			.charge(-100)
			.linkDistance(80)
			.linkStrength(.5)
			.gravity(0.02)
			.size([width, height]);

		var svg = d3.select("#swagmapcontainer").append("svg")
			.attr("width", width)
			.attr("height", height);

		$(window).resize(function() {
			$("#swagmapcontainer").height(($(window).height() - 120) + "px");
			width = $("#swagmapcontainer").width();
			height = $("#swagmapcontainer").height();

			svg.attr("width", width);
			svg.attr("height", height);
			force.size([width, height]).resume();
		});

		var dataurl = PLUGIN_URI + "/swagmapdata.php?mode=" + SWAGMAP_MODE;
		console.log("**********************************");
		console.log("loading swagmap data from: " + dataurl);

		d3.json(dataurl, function(error, json) {
			console.log("swagmap data loaded");

			if (error) throw error;

			force
				.nodes(json.nodes)
				.links(json.links)
				.start();

			var linkG = svg.selectAll(".link")
				.data(json.links)
				.enter().append("g")

			var link = linkG.append("line")
				.attr("class", "swagmap-link");

			var linkEnd = linkG.append("path")
				.attr("d", "M0,0L-10,-5L-10,5");

			var node = svg.selectAll(".node")
				.data(json.nodes)
				.enter().append("g")
				.attr("class", function(d) {
					if (d.type == "swagpath")
						return "swagmap-swagpath-node";

					return "swagmap-swag-node"
				})
				.on("click", function(d) {
					var dx = d3.event.x - d.downEvent.x;
					var dy = d3.event.x - d.downEvent.x;

					if (d.url && Math.sqrt(dx * dx + dy * dy) < 3) {
						location.href = d.url;
						//window.open(d.url);
					}
				})
				.on("mousedown", function(d) {
					d.downEvent = d3.event;
				})
				.call(force.drag);

			node.append("circle")
				.attr("r", 10)
				.attr("class", function(d) {
					if (d.completed)
						return "completed";
				})
				.attr("fill", function(d) {
					return d.color
				})
				.attr("stroke", function(d) {
					return d.color
				});

			node.append("text")
				.attr("dy", ".35em")
				.text(function(d) {
					return d.name
				});

			function vlen(x, y) {
				return Math.sqrt(x * x + y * y);
			}

			function updateLinkDataExtras(d) {
				d.angle = Math.atan2(d.target.y - d.source.y, d.target.x - d.source.x);
				d.degrees = 180 * d.angle / Math.PI;
				d.len = vlen(d.target.x - d.source.x, d.target.y - d.source.y);
				d.dx = (d.target.x - d.source.x) / d.len;
				d.dy = (d.target.y - d.source.y) / d.len;
			}

			force.on("tick", function() {

				// Make sure it doesn't dissapear.
				var firstNode = force.nodes()[0];
				var width = $("#swagmapcontainer").width();
				var height = $("#swagmapcontainer").height();

				if (firstNode.x < 0)
					firstNode.x = 0;

				if (firstNode.y < 0)
					firstNode.y = 0;

				if (firstNode.x > width)
					firstNode.x = width;

				if (firstNode.y > height)
					firstNode.y = height;

				linkEnd.attr("transform", function(d) {
					updateLinkDataExtras(d);

					var x = d.target.x - d.dx * 20;
					var y = d.target.y - d.dy * 20;

					return "translate(" + x + "," + y + ") " +
						"rotate(" + d.degrees + ")";
				});

				link.attr("x1", function(d) {
						updateLinkDataExtras(d);
						return d.source.x + d.dx * 25;
					})
					.attr("y1", function(d) {
						updateLinkDataExtras(d);
						return d.source.y + d.dy * 25;
					})
					.attr("x2", function(d) {
						updateLinkDataExtras(d);
						return d.target.x - d.dx * 25;
					})
					.attr("y2", function(d) {
						updateLinkDataExtras(d);
						return d.target.y - d.dy * 25;
					});

				node.attr("transform", function(d) {
					return "translate(" + d.x + "," + d.y + ")";
				});
			});
		});
	});
});


// This function makes sure the height of the track listing blocks are all the same height.
(function($, window, document, undefined) {
	'use strict';

	var s = document.body || document.documentElement,
		s = s.style;
	if (s.webkitFlexWrap == '' || s.msFlexWrap == '' || s.flexWrap == '') return true;

	var $list = $('.masonry-loop'),
		$items = $list.find('.track'),
		setHeights = function() {
			$items.css('height', 'auto');

			var perRow = Math.floor($list.width() / $items.width());
			if (perRow == null || perRow < 2) return true;

			for (var i = 0, j = $items.length; i < j; i += perRow) {
				var maxHeight = 0,
					$row = $items.slice(i, i + perRow);

				$row.each(function() {
					var itemHeight = parseInt($(this).outerHeight());
					if (itemHeight > maxHeight) maxHeight = itemHeight;
				});
				$row.css('height', maxHeight);
			}
		};

	setHeights();
	$(window).on('resize', setHeights);
	$list.find('img').on('load', setHeights);

})(jQuery, window, document);