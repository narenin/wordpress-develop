<?php
/**
 * @group link
 * @group comment
 * @covers ::get_next_comments_link
 */
class Tests_Link_GetEditCommentLink extends WP_UnitTestCase {

	public static $comment_ids;
	public static $user_ids;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
        self::$comment_ids = $factory->comment->create( array( 'comment_content' => 'Test comment' ) );

		self::$user_ids = array(
			'admin'      => $factory->user->create( array( 'role' => 'administrator' ) ),
			'subscriber' => $factory->user->create( array( 'role' => 'subscriber' ) ),
		);
	}

    public static function wpTearDownAfterClass() {
        // Delete the test comment
        if ( isset( self::$comment_ids ) ) {
            wp_delete_comment( self::$comment_ids, true );
        }

        // Delete the test users
        if ( isset( self::$user_ids['admin'] ) ) {
            wp_delete_user( self::$user_ids['admin'] );
        }

        if ( isset( self::$user_ids['subscriber'] ) ) {
            wp_delete_user( self::$user_ids['subscriber'] );
        }
    }

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::$user_ids['admin'] );
	}

	public function test_get_edit_comment_link_display_context() {
		$comment_id   = self::$comment_ids;
		$expected_url = admin_url( 'comment.php?action=editcomment&amp;c=' . $comment_id );
		$actual_url   = get_edit_comment_link( $comment_id, 'display' );

		$this->assertSame( $expected_url, $actual_url );
	}

	public function test_get_edit_comment_link_url_context() {
		$comment_id   = self::$comment_ids;
		$expected_url = admin_url( 'comment.php?action=editcomment&c=' . $comment_id );
		$actual_url   = get_edit_comment_link( $comment_id, '' );

		$this->assertSame( $expected_url, $actual_url );
	}

	public function test_get_edit_comment_link_invalid_comment() {
		$comment_id         = 12345;
		$actual_url_display = get_edit_comment_link( $comment_id, 'display' );
		$actual_url_view    = get_edit_comment_link( $comment_id, 'url' );

		$this->assertNull( $actual_url_display );
		$this->assertNull( $actual_url_view );
	}

	public function test_get_edit_comment_link_user_cannot_edit() {
		wp_set_current_user( self::$user_ids['subscriber'] );
		$comment_id         = self::$comment_ids;
		$actual_url_display = get_edit_comment_link( $comment_id, 'display' );
		$actual_url_view    = get_edit_comment_link( $comment_id, 'view' );

		$this->assertNull( $actual_url_display );
		$this->assertNull( $actual_url_view );
	}

	/**
	 * The test case verifies that the get_edit_comment_link function to generates comment link for editing comments,
	 * and that the URLs are correctly filtered based on context to include HTML entities in link
	 * $comment_id The ID of the comment to test, retrieved from self::$comment_ids['valid'].
	 * $expected_url The expected URL format when the context is 'display', with an HTML entity for the ampersand (&amp;).
	 * $expected_url_view The expected URL format when the context is 'view', with a regular ampersand (&).
	 * @ticket 61727
	 */

	public function test_get_edit_comment_link_filter() {
		$comment_id        = self::$comment_ids;
		$expected_url      = admin_url( 'comment-test.php?context=display' );
		$expected_url_view = admin_url( 'comment-test.php?context=view' );

		add_filter(
			'get_edit_comment_link',
            function ( $location, $comment_id, $context ) {
                return admin_url( 'comment-test.php?context=' . $context );
            },
			10,
			3
		);

		$actual_url_display = get_edit_comment_link( $comment_id, 'display' );
		$actual_url_view    = get_edit_comment_link( $comment_id, 'view' );

		// Assert the final URLs are as expected
		$this->assertSame( $expected_url, $actual_url_display );
		$this->assertSame( $expected_url_view, $actual_url_view );
	}
}
