<?php


use Silex\Application;
use Doctrine\DBAL\Connection;

/**
 * class PostsModel
 */
class PostsModel extends BaseModel
{

	private function _postsSelect()
	{
		return "SELECT post.id as id, title, content, category, time, allow_comments, slug, username, user_id
					FROM post
					JOIN user ON user.id = post.user_id";
	}



	/**
	 * Returns an arraay of posts
	 * @param  string $type The type of posts to fetch (defaults to NEWS)
	 * @param  integer $count
	 * @return array
	 */
	private function _getPosts($category = "ALL", $count = 10)
	{
		$select = $this->_postsSelect($where);

		// format the WHERE part of the statement
		if ($category != POST_TYPE_ALL) 
		{
			
			$sql = "$select 
						WHERE category = ? 
						AND display = 1";					
	
			$posts = $this->db->fetchAll($sql, array((string) $category));						
		}

		else {
			$sql = "$select 
						WHERE display = 1";					
	
			$posts = $this->db->fetchAll($sql, array((string) $category));						
		}

		// need to loop through each record and check if the post can be edited by the current user
		foreach($posts as &$post) 
		{
			if ($this->app['security']->isGranted('ROLE_USER')) {
				$user = $this->app['security']->getToken()->getUser();
				$post['can_edit'] = (( in_array('ROLE_ADMIN', $user->getRoles() )) || ($post['user_id'] == $user->getID()));
			}
			else $post['can_edit'] = false;
		}
		
    	return $posts;
	}

	/**
	 * [fetchPostsByCategory description]
	 * @param  string $category 	The category to fetch (can be ALL for fetch everything!
	 * @return array          		An arra
	 */
	public function fetchPostsByCategory($category) 
	{
		return $this->_getPosts($category);
	}

	/**
	 * Fetchs a single Post record, usually to display or edit
	 * @param  variable $id The identifier for the post.  Can be an integer (id) or a string (title)
	 * @return array     An array containing the post data
	 */
	public function fetchPost($id) {

		if (is_numeric($id)) 	$where = "post.id = $id";
		else 					$where = "post.slug = '$id'";

		$select = $this->_postsSelect($where);
		
		$sql = "$select
					WHERE $where";

		$post = $this->db->fetchAssoc($sql, array((string) $category));		

		if ($this->app['security']->isGranted('ROLE_USER')) {
			$user = $this->app['security']->getToken()->getUser();
			$post['can_edit'] = (( in_array('ROLE_ADMIN', $user->getRoles() )) || ($post['user_id'] == $user->getID()));
		}
		else $post['can_edit'] = false;				

		return $post; 				
	}

	/**
	 * Updates a POST record
	 * @param  integer $id   	id of the post to be updated
	 * @param  array $data   	an array containing the updated data (usually created by a Silex Form)
	 * @return handle      		handle to the PostController object
	 */
	public function updatePost($id, $data) 
	{
		$this->db->update('post', array(
            'title'             => $data['title'],
            'slug'          => $data['slug'],
            'content'           => $data['content'],
            'category'          => $data['category'],
            'allow_comments'    => $data['allow_comments'],
            ), array('id' => $id));

		return $this;
	}

	public function insertPost($data) 
	{
		$this->db->insert('post', array(
			'title' 			=> $data['title'],
			'slug' 			=> $data['slug'],
			'content' 			=> $data['content'],
			'category' 			=> $data['category'],
			'allow_comments' 	=> $data['allow_comments'],
			'user_id' 			=> $data['user_id'],
		));

		return $this->db->lastInsertId();
	}

	public function createForm($data, $categoryChoices) 
	{
		return $this->app['form.factory']->createBuilder('form', $data)
	        ->add('title')
	        ->add('slug')
	        ->add('content', 'textarea', array(
	            'attr' => array('class' => 'html-editor'),
	        ))
	        ->add('category', 'choice', array(
	            'choices' => $categoryChoices,
	            'expanded' => false,
	            'multiple' => false,
	        ))
	        ->add('allow_comments', 'choice', array(
	            'choices' => array(1 => 'Yes', 0 => 'No'),
	            'expanded' => false,
	        ))
	        ->getForm();
	}
}

