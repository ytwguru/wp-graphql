<?php

class NodesTest extends \Codeception\TestCase\WPTestCase {

	public $admin;

	public function setUp() {
		// before
		parent::setUp();

		$this->admin = $this->factory()->user->create( [
			'role' => 'administrator'
		] );
	}

	public function tearDown() {

		// then
		parent::tearDown();
	}

	public function testNodeQueryWithVariables() {

		/**
		 * Set up the $args
		 */
		$args = array(
			'post_status'  => 'publish',
			'post_content' => 'Test page content',
			'post_title'   => 'Test Page Title',
			'post_type'    => 'page',
			'post_author'  => $this->admin,
		);

		/**
		 * Create the page
		 */
		$page_id = $this->factory->post->create( $args );

		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'page', $page_id );

		wp_set_current_user( $this->admin );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = '
		query getPageByNode( $id:ID! ) { 
			node( id:$id ) { 
				__typename 
				...on Page {
					pageId
				}
			} 
		}';

		$variables = wp_json_encode( [
			'id' => $global_id,
		] );

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query, 'getPageByNode', $variables );

		codecept_debug( $actual );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Page',
					'pageId'     => $page_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * testPageNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testPageNodeQuery() {

		/**
		 * Set up the $args
		 */
		$args = array(
			'post_status'  => 'publish',
			'post_content' => 'Test page content',
			'post_title'   => 'Test Page Title',
			'post_type'    => 'page',
			'post_author'  => $this->admin,
		);

		/**
		 * Create the page
		 */
		$page_id = $this->factory->post->create( $args );

		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'page', $page_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename 
				...on Page {
					pageId
				}
			} 
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query, '', '' );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Page',
					'pageId'     => $page_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPostNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testPostNodeQuery() {

		$args = array(
			'post_status'  => 'publish',
			'post_content' => 'Test post content',
			'post_title'   => 'Test post Title',
			'post_type'    => 'post',
			'post_author'  => $this->admin,
		);

		$post_id = $this->factory->post->create( $args );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'post', $post_id );

		$query  = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename
				... on Post {
					postId
				}
			} 
		}";
		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Post',
					'postId'     => $post_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testAttachmentNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testAttachmentNodeQuery() {

		$args = array(
			'post_status'  => 'inherit',
			'post_content' => 'Test attachment content',
			'post_title'   => 'Test attachment Title',
			'post_type'    => 'attachment',
			'post_author'  => $this->admin,
		);

		$attachment_id = $this->factory->post->create( $args );
		$global_id     = \GraphQLRelay\Relay::toGlobalId( 'attachment', $attachment_id );
		$query         = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename
				...on MediaItem {
					mediaItemId
				}
			} 
		}";
		$actual        = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename'  => 'MediaItem',
					'mediaItemId' => $attachment_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPluginNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testPluginNodeQuery() {

		$plugin_name = 'Hello Dolly';
		$global_id   = \GraphQLRelay\Relay::toGlobalId( 'plugin', $plugin_name );
		$query       = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename
				... on Plugin {
					name
				}
			} 
		}";

		wp_set_current_user( $this->admin );
		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Plugin',
					'name'       => $plugin_name,
				],
			],
		];

		$this->assertEquals( $expected, $actual, "Verify you have the plugin Hello Dolly in your WordPress." );
	}

	/**
	 * testThemeNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testThemeNodeQuery() {

		$theme_slug = 'twentyseventeen';
		$global_id  = \GraphQLRelay\Relay::toGlobalId( 'theme', $theme_slug );
		$query      = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename 
				...on Theme{ 
					slug 
				} 
			} 
		}";
		wp_set_current_user( $this->admin );
		$actual     = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Theme',
					'slug'       => $theme_slug,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserNodeQuery
	 *
	 * @dataProvider dataProviderUserNode
	 *
	 * @param bool $has_posts whether or not the user has published posts
	 *
	 * @since 0.0.5
	 */
	public function testUserNodeQuery( $has_posts, $user, $private ) {

		$user_args = array(
			'role'       => 'editor',
			'user_email' => 'graphqliscool@wpgraphql.com',
		);

		$user_id = $this->factory->user->create( $user_args );

		if ( true === $has_posts ) {
			$this->factory()->post->create( [
				'post_author' => $user_id,
			] );
		}

		if ( ! empty( $user ) ) {

			switch ( $user ) {
				case 'admin':
					$current_user = $this->admin;
					break;
				case 'owner':
					$current_user = $user_id;
					break;
				default:
					$current_user = 0;
					break;
			}

			wp_set_current_user( $current_user );

		}

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );
		$query     = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename 
				...on User{ 
					userId 
				} 
			} 
		}
		";
		$actual    = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'User',
					'userId'     => $user_id,
				],
			],
		];

		if ( true === $private ) {
			$expected['data']['node']['userId'] = null;
		}

		$this->assertEquals( $expected, $actual );
	}

	public function dataProviderUserNode() {
		return [
			[
				'has_posts' => true,
				'user' => '',
				'private' => false,
			],
			[
				'has_posts' => false,
				'user' => '',
				'private' => true,
			],
			[
				'has_posts' => false,
				'user' => 'admin',
				'private' => false,
			],
			[
				'has_posts' => false,
				'user' => 'owner',
				'private' => false,
			]
		];
	}

	/**
	 * testCommentNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testCommentNodeQuery() {

		$user_args = array(
			'role'       => 'editor',
			'user_email' => 'graphqliscool@wpgraphql.com',
		);

		$user_id = $this->factory->user->create( $user_args );

		$comment_args = array(
			'user_id'         => $user_id,
			'comment_content' => 'GraphQL is really awesome, dude!',
		);
		$comment_id   = $this->factory->comment->create( $comment_args );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'comment', $comment_id );
		$query     = "
		query { 
			node(id: \"{$global_id}\") {
				__typename 
				...on Comment{ 
					commentId 
				} 
			} 
		}
		";
		$actual    = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Comment',
					'commentId'  => $comment_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test that a comment author can be retrieved in a single node
	 */
	public function testCommentAuthorQuery() {

		$comment_args = [
			'comment_author_email' => 'yoyoyo@wpgraphql.com',
			'comment_author' => 'Test Author',
			'comment_author_url' => 'wpgraphql.com',
			'comment_content' => 'JsOnB00l smellz',
		];

		$comment_id = $this->factory->comment->create( $comment_args );
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'commentAuthor', $comment_id );

		$query = "
		query {
			node(id: \"{$global_id}\") {
				__typename
				...on CommentAuthor {
					id
				}
			}
		}
		";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'CommentAuthor',
					'id' => $global_id,
				]
			]
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests querying for a single post node
	 */
	public function testSuccessfulPostTypeResolver() {

		$query = "
		{
		  node(id:\"Y29udGVudFR5cGU6cG9zdA==\"){
			...on ContentType {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'name' => 'post',
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests querying for a single post node where the post id doesn't exist
	 */
	public function testUnsuccessfulPostTypeResolver() {

		$query = "
		{
		  node(id:\"Y29udGVudFR5cGU6dGVzdA==\"){
			...on ContentType {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Tests querying for a single taxonomy node
	 */
	public function testSuccessfulTaxonomyResolver() {

		$query = "
		{
		  node(id:\"dGF4b25vbXk6Y2F0ZWdvcnk=\"){
			...on Taxonomy {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'name' => 'category',
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests querying for a single taxonomy node where the ID doesn't exist
	 */
	public function testUnsuccessfulTaxonomyResolver() {

		$query = "
		{
		  node(id:\"dGF4b25vbXk6dGVzdA==\"){
			...on Taxonomy {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Tests querying for a single comment node where the comment ID doesn't exist
	 */
	public function testUnsuccessfulCommentResolver() {

		$query = "
		{
		  node(id:\"nonExistentId\"){
			...on Comment {
			  id
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

}
