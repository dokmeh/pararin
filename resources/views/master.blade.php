<!DOCTYPE html>
<html>
<head>
    <title>{!! $data['title'] !!}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="{!! url('/') !!}/">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/loading.css" rel="stylesheet">
    <link href="css/perfect-scrollbar.min.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.touchSwipe.min.js"></script>
    <script src="js/perfect-scrollbar.jquery.min.js"></script>
    <script src="js/script.js"></script>
</head>

<body data-page="{!! $data['page'] !!}" data-device="{!! Agent::isMobile() ? (Agent::isTablet() ? 'tablet' : 'phone') : 'computer' !!}"{!! $data['page'] == 'home' ? ' class="enter-mode"' : '' !!}>
    <div class="container">
        <section class="content">
            {!! $data['content'] !!}
        </section>
        <nav class="menu-container">
            <img class="menu-logo" src="img/logo.svg">
            <nav class="menu">
                <ul class="menu-ul">
                    <li class="menu-li"><a class="menu-a" href="{!! url('/') !!}/">Home</a></li>
                    <li class="menu-li"><a class="menu-a" href="projects">Projects</a></li>
                    <li class="menu-li"><a class="menu-a" href="awards">Awards</a></li>
                    <li class="menu-li"><a class="menu-a" href="publications">Pubications</a></li>
                    <li class="menu-li"><a class="menu-a" href="news">News</a></li>
                    <li class="menu-li"><a class="menu-a" href="contact">Contact us</a></li>
                </ul>
            </nav>
        </nav>
    </div>
    @if($data['page'] == 'home')
        <div class="enter">
            <img class="enter-logo" src="img/logo.svg">
            <p class="enter-text">Enter</p>
        </div>
    @endif
    <script src="http://threejs.org/build/three.js"></script>
    <script src="http://threejs.org/examples/js/Detector.js"></script>
    <script src="http://threejs.org/examples/js/libs/dat.gui.min.js"></script>
    <script src="http://threejs.org/examples/js/GPUComputationRenderer.js"></script>
    <script id="fragmentShaderPosition" type="x-shader/x-fragment">
			uniform float time;
			uniform float delta;
			void main()	{
				vec2 uv = gl_FragCoord.xy / resolution.xy;
				vec4 tmpPos = texture2D( texturePosition, uv );
				vec3 position = tmpPos.xyz;
				vec3 velocity = texture2D( textureVelocity, uv ).xyz;
				float phase = tmpPos.w;
				phase = mod( ( phase + delta +
					length( velocity.xz ) * delta * 3. +
					max( velocity.y, 0.0 ) * delta * 6. ), 62.83 );
				gl_FragColor = vec4( position + velocity * delta * 15. , phase );
			}
		</script>
    <script id="fragmentShaderVelocity" type="x-shader/x-fragment">
			uniform float time;
			uniform float testing;
			uniform float delta;
			uniform float seperationDistance;
			uniform float alignmentDistance;
			uniform float cohesionDistance;
			uniform float freedomFactor;
			uniform vec3 predator;
			const float width = resolution.x;
			const float height = resolution.y;
			const float PI = 3.141592653589793;
			const float PI_2 = PI * 2.0;
			float zoneRadius = 40.0;
			float zoneRadiusSquared = 1600.0;
			float separationThresh = 0.45;
			float alignmentThresh = 0.65;
			const float UPPER_BOUNDS = BOUNDS;
			const float LOWER_BOUNDS = -UPPER_BOUNDS;
			const float SPEED_LIMIT = 9.0;
			float rand(vec2 co){
				return fract(sin(dot(co.xy ,vec2(12.9898,78.233))) * 43758.5453);
			}
			void main() {
				zoneRadius = seperationDistance + alignmentDistance + cohesionDistance;
				separationThresh = seperationDistance / zoneRadius;
				alignmentThresh = ( seperationDistance + alignmentDistance ) / zoneRadius;
				zoneRadiusSquared = zoneRadius * zoneRadius;
				vec2 uv = gl_FragCoord.xy / resolution.xy;
				vec3 birdPosition, birdVelocity;
				vec3 selfPosition = texture2D( texturePosition, uv ).xyz;
				vec3 selfVelocity = texture2D( textureVelocity, uv ).xyz;
				float dist;
				vec3 dir;
				float distSquared;
				float seperationSquared = seperationDistance * seperationDistance;
				float cohesionSquared = cohesionDistance * cohesionDistance;
				float f;
				float percent;
				vec3 velocity = selfVelocity;
				float limit = SPEED_LIMIT;
				dir = predator * UPPER_BOUNDS - selfPosition;
				dir.z = 0.;
				dist = length( dir );
				distSquared = dist * dist;
				float preyRadius = 150.0;
				float preyRadiusSq = preyRadius * preyRadius;
				if (dist < preyRadius) {
					f = ( distSquared / preyRadiusSq - 1.0 ) * delta * 100.;
					velocity += normalize( dir ) * f;
					limit += 5.0;
				}
				vec3 central = vec3( 0., 0., 0. );
				dir = selfPosition - central;
				dist = length( dir );
				dir.y *= 2.5;
				velocity -= normalize( dir ) * delta * 5.;
				for (float y=0.0;y<height;y++) {
					for (float x=0.0;x<width;x++) {
						vec2 ref = vec2( x + 0.5, y + 0.5 ) / resolution.xy;
						birdPosition = texture2D( texturePosition, ref ).xyz;
						dir = birdPosition - selfPosition;
						dist = length(dir);
						if (dist < 0.0001) continue;
						distSquared = dist * dist;
						if (distSquared > zoneRadiusSquared ) continue;
						percent = distSquared / zoneRadiusSquared;
						if ( percent < separationThresh ) {
							f = (separationThresh / percent - 1.0) * delta;
							velocity -= normalize(dir) * f;
						} else if ( percent < alignmentThresh ) {
							float threshDelta = alignmentThresh - separationThresh;
							float adjustedPercent = ( percent - separationThresh ) / threshDelta;
							birdVelocity = texture2D( textureVelocity, ref ).xyz;
							f = ( 0.5 - cos( adjustedPercent * PI_2 ) * 0.5 + 0.5 ) * delta;
							velocity += normalize(birdVelocity) * f;
						} else {
							float threshDelta = 1.0 - alignmentThresh;
							float adjustedPercent = ( percent - alignmentThresh ) / threshDelta;
							f = ( 0.5 - ( cos( adjustedPercent * PI_2 ) * -0.5 + 0.5 ) ) * delta;
							velocity += normalize(dir) * f;
						}
					}
				}
				if ( length( velocity ) > limit ) {
					velocity = normalize( velocity ) * limit;
				}
				gl_FragColor = vec4( velocity, 1.0 );
			}
		</script>
    <script type="x-shader/x-vertex" id="birdVS">
			attribute vec2 reference;
			attribute float birdVertex;
			attribute vec3 birdColor;
			uniform sampler2D texturePosition;
			uniform sampler2D textureVelocity;
			varying vec4 vColor;
			varying float z;
			uniform float time;
			void main() {
				vec4 tmpPos = texture2D( texturePosition, reference );
				vec3 pos = tmpPos.xyz;
				vec3 velocity = normalize(texture2D( textureVelocity, reference ).xyz);
				vec3 newPosition = position;
				if ( birdVertex == 4.0 || birdVertex == 7.0 ) {
					newPosition.y = sin( tmpPos.w ) * 5.;
				}
				newPosition = mat3( modelMatrix ) * newPosition;
				velocity.z *= -1.;
				float xz = length( velocity.xz );
				float xyz = 1.;
				float x = sqrt( 1. - velocity.y * velocity.y );
				float cosry = velocity.x / xz;
				float sinry = velocity.z / xz;
				float cosrz = x / xyz;
				float sinrz = velocity.y / xyz;
				mat3 maty =  mat3(
					cosry, 0, -sinry,
					0    , 1, 0     ,
					sinry, 0, cosry
				);
				mat3 matz =  mat3(
					cosrz , sinrz, 0,
					-sinrz, cosrz, 0,
					0     , 0    , 1
				);
				newPosition =  maty * matz * newPosition;
				newPosition += pos;
				z = newPosition.z;
				vColor = vec4( birdColor, 1.0 );
				gl_Position = projectionMatrix *  viewMatrix  * vec4( newPosition, 1.0 );
			}
		</script>
    <script type="x-shader/x-fragment" id="birdFS">
			varying vec4 vColor;
			varying float z;
			uniform vec3 color;
			void main() {
				float z2 = 0.2 + ( 1000. - z ) / 1000. * vColor.x;
				gl_FragColor = vec4( z2, z2, z2, 1. );
			}
		</script>
    <script>
        if ( ! Detector.webgl ) Detector.addGetWebGLMessage();
        var hash = document.location.hash.substr( 1 );
        if (hash) hash = parseInt(hash, 0);
        var WIDTH = hash || 32;
        var BIRDS = WIDTH * WIDTH;
        THREE.BirdGeometry = function () {
            var triangles = BIRDS * 3;
            var points = triangles * 3;
            THREE.BufferGeometry.call( this );
            var vertices = new THREE.BufferAttribute( new Float32Array( points * 3 ), 3 );
            var birdColors = new THREE.BufferAttribute( new Float32Array( points * 3 ), 3 );
            var references = new THREE.BufferAttribute( new Float32Array( points * 2 ), 2 );
            var birdVertex = new THREE.BufferAttribute( new Float32Array( points ), 1 );
            this.addAttribute( 'position', vertices );
            this.addAttribute( 'birdColor', birdColors );
            this.addAttribute( 'reference', references );
            this.addAttribute( 'birdVertex', birdVertex );
            var v = 0;
            function verts_push() {
                for (var i=0; i < arguments.length; i++) {
                    vertices.array[v++] = arguments[i];
                }
            }
            var wingsSpan = 20;
            for (var f = 0; f<BIRDS; f++ ) {
                verts_push(
                        0, -0, -20,
                        0, 4, -20,
                        0, 0, 30
                );
                verts_push(
                        0, 0, -15,
                        -wingsSpan, 0, 0,
                        0, 0, 15
                );
                verts_push(
                        0, 0, 15,
                        wingsSpan, 0, 0,
                        0, 0, -15
                );
            }
            for( var v = 0; v < triangles * 3; v++ ) {
                var i = ~~(v / 3);
                var x = (i % WIDTH) / WIDTH;
                var y = ~~(i / WIDTH) / WIDTH;
                var c = new THREE.Color(
                        0x444444 +
                        ~~(v / 9) / BIRDS * 0x666666
                );
                birdColors.array[ v * 3 + 0 ] = c.r;
                birdColors.array[ v * 3 + 1 ] = c.g;
                birdColors.array[ v * 3 + 2 ] = c.b;
                references.array[ v * 2     ] = x;
                references.array[ v * 2 + 1 ] = y;
                birdVertex.array[ v         ] = v % 9;
            }
            this.scale( 0.2, 0.2, 0.2 );
        };
        THREE.BirdGeometry.prototype = Object.create( THREE.BufferGeometry.prototype );
        var container, stats;
        var camera, scene, renderer, geometry, i, h, color;
        var mouseX = 0, mouseY = 0;
        var windowHalfX = window.innerWidth / 2;
        var windowHalfY = window.innerHeight / 2;
        var BOUNDS = 800, BOUNDS_HALF = BOUNDS / 2;
        function change(n) {
            location.hash = n;
            location.reload();
            return false;
        }
        var last = performance.now();
        var gpuCompute;
        var velocityVariable;
        var positionVariable;
        var positionUniforms;
        var velocityUniforms;
        var birdUniforms;
        init();
        animate();
        function init() {
            $('body').prepend('<div class="bg"></div>');
            camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 1, 3000 );
            camera.position.z = 350;
            scene = new THREE.Scene();
            scene.fog = new THREE.Fog( 0xffffff, 100, 1000 );
            renderer = new THREE.WebGLRenderer();
            renderer.setClearColor( scene.fog.color );
            renderer.setPixelRatio( window.devicePixelRatio );
            renderer.setSize( window.innerWidth, window.innerHeight );
            $('.bg').append(renderer.domElement);
            initComputeRenderer();
            document.addEventListener( 'mousemove', onDocumentMouseMove, false );
            document.addEventListener( 'touchstart', onDocumentTouchStart, false );
            document.addEventListener( 'touchmove', onDocumentTouchMove, false );
            window.addEventListener( 'resize', onWindowResize, false );
            var effectController = {
                seperation: 20.0,
                alignment: 20.0,
                cohesion: 20.0,
                freedom: 0.75
            };
            var valuesChanger = function() {

                velocityUniforms.seperationDistance.value = effectController.seperation;
                velocityUniforms.alignmentDistance.value = effectController.alignment;
                velocityUniforms.cohesionDistance.value = effectController.cohesion;
                velocityUniforms.freedomFactor.value = effectController.freedom;

            };
            valuesChanger();
            initBirds();
        }
        function initComputeRenderer() {
            gpuCompute = new GPUComputationRenderer( WIDTH, WIDTH, renderer );
            var dtPosition = gpuCompute.createTexture();
            var dtVelocity = gpuCompute.createTexture();
            fillPositionTexture( dtPosition );
            fillVelocityTexture( dtVelocity );
            velocityVariable = gpuCompute.addVariable( "textureVelocity", document.getElementById( 'fragmentShaderVelocity' ).textContent, dtVelocity );
            positionVariable = gpuCompute.addVariable( "texturePosition", document.getElementById( 'fragmentShaderPosition' ).textContent, dtPosition );
            gpuCompute.setVariableDependencies( velocityVariable, [ positionVariable, velocityVariable ] );
            gpuCompute.setVariableDependencies( positionVariable, [ positionVariable, velocityVariable ] );
            positionUniforms = positionVariable.material.uniforms;
            velocityUniforms = velocityVariable.material.uniforms;
            positionUniforms.time = { value: 0.0 };
            positionUniforms.delta = { value: 0.0 };
            velocityUniforms.time = { value: 1.0 };
            velocityUniforms.delta = { value: 0.0 };
            velocityUniforms.testing = { value: 1.0 };
            velocityUniforms.seperationDistance = { value: 1.0 };
            velocityUniforms.alignmentDistance = { value: 1.0 };
            velocityUniforms.cohesionDistance = { value: 1.0 };
            velocityUniforms.freedomFactor = { value: 1.0 };
            velocityUniforms.predator = { value: new THREE.Vector3() };
            velocityVariable.material.defines.BOUNDS = BOUNDS.toFixed( 2 );
            velocityVariable.wrapS = THREE.RepeatWrapping;
            velocityVariable.wrapT = THREE.RepeatWrapping;
            positionVariable.wrapS = THREE.RepeatWrapping;
            positionVariable.wrapT = THREE.RepeatWrapping;
            var error = gpuCompute.init();
            if ( error !== null ) {
                console.error( error );
            }
        }
        function initBirds() {
            var geometry = new THREE.BirdGeometry();
            birdUniforms = {
                color: { value: new THREE.Color( 0xff2200 ) },
                texturePosition: { value: null },
                textureVelocity: { value: null },
                time: { value: 1.0 },
                delta: { value: 0.0 }
            };
            var material = new THREE.ShaderMaterial( {
                uniforms:       birdUniforms,
                vertexShader:   document.getElementById( 'birdVS' ).textContent,
                fragmentShader: document.getElementById( 'birdFS' ).textContent,
                side: THREE.DoubleSide

            });
            birdMesh = new THREE.Mesh( geometry, material );
            birdMesh.rotation.y = Math.PI / 2;
            birdMesh.matrixAutoUpdate = false;
            birdMesh.updateMatrix();
            scene.add(birdMesh);
        }
        function fillPositionTexture( texture ) {
            var theArray = texture.image.data;
            for ( var k = 0, kl = theArray.length; k < kl; k += 4 ) {
                var x = Math.random() * BOUNDS - BOUNDS_HALF;
                var y = Math.random() * BOUNDS - BOUNDS_HALF;
                var z = Math.random() * BOUNDS - BOUNDS_HALF;
                theArray[ k + 0 ] = x;
                theArray[ k + 1 ] = y;
                theArray[ k + 2 ] = z;
                theArray[ k + 3 ] = 1;
            }
        }
        function fillVelocityTexture( texture ) {
            var theArray = texture.image.data;
            for ( var k = 0, kl = theArray.length; k < kl; k += 4 ) {
                var x = Math.random() - 0.5;
                var y = Math.random() - 0.5;
                var z = Math.random() - 0.5;
                theArray[ k + 0 ] = x * 10;
                theArray[ k + 1 ] = y * 10;
                theArray[ k + 2 ] = z * 10;
                theArray[ k + 3 ] = 1;
            }
        }
        function onWindowResize() {
            windowHalfX = window.innerWidth / 2;
            windowHalfY = window.innerHeight / 2;
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize( window.innerWidth, window.innerHeight );
        }
        function onDocumentMouseMove( event ) {
            mouseX = event.clientX - windowHalfX;
            mouseY = event.clientY - windowHalfY;
        }

        function onDocumentTouchStart( event ) {
            if ( event.touches.length === 1 ) {
                event.preventDefault();
                mouseX = event.touches[ 0 ].pageX - windowHalfX;
                mouseY = event.touches[ 0 ].pageY - windowHalfY;

            }
        }
        function onDocumentTouchMove( event ) {
            if ( event.touches.length === 1 ) {
                event.preventDefault();
                mouseX = event.touches[ 0 ].pageX - windowHalfX;
                mouseY = event.touches[ 0 ].pageY - windowHalfY;
            }
        }
        function animate() {
            requestAnimationFrame( animate );
            render();
        }
        function render() {
            var now = performance.now();
            var delta = (now - last) / 1000;
            if (delta > 1) delta = 1;
            last = now;
            positionUniforms.time.value = now;
            positionUniforms.delta.value = delta;
            velocityUniforms.time.value = now;
            velocityUniforms.delta.value = delta;
            birdUniforms.time.value = now;
            birdUniforms.delta.value = delta;
            velocityUniforms.predator.value.set( 0.5 * mouseX / windowHalfX, - 0.5 * mouseY / windowHalfY, 0 );
            mouseX = 10000;
            mouseY = 10000;
            gpuCompute.compute();
            birdUniforms.texturePosition.value = gpuCompute.getCurrentRenderTarget( positionVariable ).texture;
            birdUniforms.textureVelocity.value = gpuCompute.getCurrentRenderTarget( velocityVariable ).texture;
            renderer.render( scene, camera );
        }
    </script>
</body>
</html>