<?php
/**
 * Course Template
 **/
class CoursePress_Template_Course {
	public static function course_instructors() {
		$content = '[COURSE INSTRUCTORS]';

		return $content;
	}

	public static function course_archive() {
		return do_shortcode( '[course_archive]' );
	}

	public static function course() {
		return do_shortcode( '[course_page]' );
	}

	public static function course_list_table( $courses = array() ) {
		if ( ! is_array( $courses ) || empty( $courses ) ) {
			return '';
		}

		$content = '';
		$student_id = get_current_user_id();
		$courses = array_filter( $courses );

		if ( ! empty( $courses ) ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			$table_header = '';
			$table_body = '';

			$table_columns = array(
				'name' => __( 'Course', 'cp' ),
				'date_enrolled' => __( 'Date Enrolled', 'cp' ),
				'average' => __( 'Average', 'cp' ),
				'certificate' => __( 'Certificate', 'cp' ),
			);

			foreach ( $table_columns as $column => $column_label ) {
				$table_header .= sprintf( '<th class="column-%s">%s</th>', $column, $column_label );
			}

			$column_keys = array_keys( $table_columns );

			foreach ( $courses as $course ) {
				$course_url = CoursePress_Data_Course::get_course_url( $course->ID );
				$course_completed = CoursePress_Data_Student::is_course_complete( $student_id, $course->ID );

				$table_body .= '<tr>';

				foreach ( $column_keys as $column_key ) {
					switch ( $column_key ) {
						case 'name':
							$workbook_url = CoursePress_Data_Student::get_workbook_url( $course->ID );
							$workbook_link = sprintf( '<a href="%s">%s</a>', esc_url( $workbook_url ), __( 'Workbook', 'cp' ) );

							$row_actions = array(
								'workbook' => $workbook_link,
								'view' => sprintf( '<a href="%s">%s</a>', esc_attr( $course_url ), __( 'View Course', 'cp' ) ),
							);

							if ( CoursePress_Data_Capabilities::can_update_course( $course->ID, $student_id ) ) {
								$edit_link = add_query_arg(
									array(
										'page' => 'coursepress_course',
										'action' => 'edit',
										'id' => $course->ID,
									)
								);
								$edit_link = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), __( 'Edit', 'cp' ) );
								array_unshift( $row_actions, $edit_link );
							}

							$withdraw_link = add_query_arg( array(
								'_wpnonce' => wp_create_nonce( 'coursepress_student_withdraw' ),
								'course_id' => $course->ID,
								'student_id' => $student_id,
							) );
							$withdraw_link = sprintf( '<a href="%s">%s</a>', esc_url( $withdraw_link ), __( 'Withdraw', 'cp' ) );
							$row_actions['withdraw'] = $withdraw_link;

							$row_actions = sprintf( '<div class="row-actions">%s</div>', implode( ' | ', $row_actions ) );

							$table_body = sprintf( '<td><a href="%s">%s</a>%s</td>', esc_url( $course_url ), $course->post_title, $row_actions );
							break;

						case 'date_enrolled':
							$date_enrolled = get_user_meta( $student_id, 'enrolled_course_date_' . $course->ID );

							if ( is_array( $date_enrolled ) ) {
								$date_enrolled = array_pop( $date_enrolled );
							}
							$date_enrolled = date_i18n( $date_format, CoursePress_Data_Course::strtotime( $date_enrolled ) );

							$table_body .= sprintf( '<td>%s</td>', $date_enrolled );
							break;

						case 'average':
							$average = CoursePress_Data_Student::average_course_responses( $student_id, $course->ID );
							$table_body .= sprintf( '<td>%s%s</td>', $average, '%' );
							break;

						case 'certificate':
							$download_certificate = __( 'Not available', 'cp' );

							if ( $course_completed ) {
								$certificate_link = CoursePress_Data_Certificate::get_encoded_url( $course->ID, $student_id );
								$download_certificate = sprintf( '<a href="%s" class="button-primary">%s</a>', $certificate_link, __( 'Download', 'cp' ) );
							}

							$table_body .= sprintf( '<td>%s</td>', $download_certificate );
							break;
					}
				}

				$table_body .= '</tr>';
			}

			$table_format = '<table><thead><tr>%s</tr></thead><tbody>%s</tbody></table>';

			$content .= sprintf( $table_format, $table_header, $table_body );
		}

		return $content;
	}

	/**
	 * Template for instructor of facilitator pending avatar.
	 *
	 * @since 2.0.0
	 * @param array $invite Invitation data.
	 * @param boolean $remove_buttons Show or hide remove button.
	 * @param string $type Instructor or facilitator.
	 * @return string Content.
	 */
	public static function course_edit_avatar( $user, $remove_buttons = false, $type = 'instructor' ) {
		$content = '';
		/**
		 * check type!
		 */
		if ( '{{{data.who}}}' != $type && ! preg_match( '/^(instructor|facilitator)$/', $type ) ) {
			return $content;
		}
		$id = '';
		if ( $remove_buttons ) {
			$id = sprintf(
				'id="%s_holder_%s"',
				esc_attr( $type ),
				esc_attr( $user->ID )
			);
		}
			$content = sprintf(
				'<div class="avatar-holder %s-avatar-holder" data-who="%s" data-id="%s" data-status="confirmed" %s>',
				esc_attr( $type ),
				esc_attr( $type ),
				esc_attr( $user->ID ),
				$id
			);
			$content .= sprintf(
				'<div class="%s-status"></div>',
				esc_attr( $type )
			);
			if ( $remove_buttons ) {
				$content .= '<div class="remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>';
			}
			if ( is_numeric( $user->ID ) ) {
				$content .= get_avatar( $user->ID, 80 );
			} else {
				$content .= '{{{data.avatar}}}';
			}
			$content .= sprintf(
				'<span class="%s-name">%s</span>',
				esc_attr( $type ),
				esc_attr( $user->display_name )
			);
			$content .= '</div>';
			return $content;
	}

	/**
	 * Template for instructor of facilitator pending avatar.
	 *
	 * @since 2.0.0
	 * @param array $invite Invitation data.
	 * @param boolean $remove_buttons Show or hide remove button.
	 * @param string $type Instructor or facilitator.
	 * @return string Content.
	 */
	public static function course_edit_avatar_pending_invite( $invite, $remove_buttons = false, $type = 'instructor' ) {
		$content = '';
		if ( empty( $invite ) ) {
			return $content;
		}
		/**
		 * check type!
		 */
		if ( '{{{data.who}}}' != $type && ! preg_match( '/^(instructor|facilitator)$/', $type ) ) {
			return $content;
		}
		$id = '';
		if ( $remove_buttons ) {
			$id = sprintf(
				'id="%s_holder_%s"',
				esc_attr( $type ),
				isset( $invite['code'] )? esc_attr( $invite['code'] ) : ''
			);
		}
			$content = sprintf(
				'<div class="avatar-holder %s-avatar-holder pending-invite" data-who="%s" data-code="%s" data-status="pending" %s>',
				esc_attr( $type ),
				esc_attr( $type ),
				isset( $invite['code'] )? esc_attr( $invite['code'] ):'',
				$id
			);
			$content .= sprintf(
				'<div class="%s-status">%s</div>',
				esc_attr( $type ),
				esc_html__( 'Pending', 'cp' )
			);
			if ( $remove_buttons ) {
				$content .= '<div class="remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>';
			}
			if ( isset( $invite['email'] ) ) {
				if ( '{{{data.avatar}}}' == $invite['email'] ) {
					$content .= $invite['email'];
				} else {
					$content .= get_avatar( $invite['email'], 80 );
				}
			}
			$content .= sprintf(
				'<span class="%s-name">%s %s</span>',
				esc_attr( $type ),
				isset( $invite['first_name'] )? $invite['first_name']:'',
				isset( $invite['last_name'] )? $invite['last_name']:''
			);
			$content .= '</div>';
			return $content;
	}

	/**
	 * JavaScript template for invited person.
	 *
	 * @since 2.0.0
	 *
	 * @return string Invitation template.
	 */
	public static function javascript_templates() {
		$invite = array(
			'code' => '{{{data.code}}}',
			'first_name' => '{{{data.first_name}}}',
			'last_name' => '{{{data.last_name}}}',
			'email' => '{{{data.avatar}}}',
		);
		/**
		 * Invitation template
		 */
		$content = '<script type="text/html" id="tmpl-course-invitation">';
		$content .= self::course_edit_avatar_pending_invite( $invite, true, '{{{data.who}}}' );
		$content .= '</script>';

		/**
		 * User template
		 */
		$invite = array(
			'ID' => '{{{data.id}}}',
			'display_name' => '{{{data.display_name}}}',
			'avatar' => '{{{data.avatar}}}',
			'course_id' => '{{{data.course_id}}}',
		);
		$user = (object) $invite;
		$content .= '<script type="text/html" id="tmpl-course-person">';
		$content .= self::course_edit_avatar( $user, true, '{{{data.who}}}' );
		$content .= '</script>';
		return $content;
	}

}
